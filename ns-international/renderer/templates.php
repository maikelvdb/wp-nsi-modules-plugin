<?php
include_once 'functions/includes.php';

class NsiTemplateParser
{
    private $basePath;
    private array $builtInFunctions;

    public function __construct($basePath)
    {
        $this->basePath = rtrim($basePath, '/') . '/';
        $this->initializeBuiltInFunctions();
    }

    public function render($templateName, $data)
    {
        $output = $this->loadTemplate($templateName);
        $styles = $this->loadStyles($templateName);

        list($output, $variables) = $this->processVariableDefinitions($output, $data);

        $output = $this->processLoops($output, $data, $variables);
        $output = $this->resolveBuiltInFunctions($output, $data, $variables);

        $output = $this->processConditions($output, $data, $variables);
        $output = $this->replaceTextVariables($output, $data, $variables);

        $output = $this->renderElements($output);

        //$output .= $this->addDataToConsoleLogger($data, $variables);

        if ($styles) {
            $output = "<style>" . $styles . "</style>\n" . $output;
        }

        return $output;
    }
    
    private function addDataToConsoleLogger($data, $variables = [])
    {
        $dataJson = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $variablesJson = json_encode($variables, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return "<script>console.log('Template Data:', $dataJson); console.log('Template Variables:', $variablesJson);</script>";
    }

    private function loadTemplate($templateName)
    {
        $templatePath = $this->basePath . $templateName . '/index.html';
        if (!file_exists($templatePath)) {
            die("Template not found: $templatePath");
        }

        return file_get_contents($templatePath);
    }

    private function loadStyles($templateName)
    {
        $templatePath = $this->basePath . $templateName . '/style.css';
        if (!file_exists($templatePath)) {
            return null;
        }

        return file_get_contents($templatePath);
    }

    private function processVariableDefinitions($content, $data)
    {
        $variables = [
            'PLUGIN_PATH' => NSI_PLUGIN_URL,
        ];

        if (preg_match('/<!--\s*!!SET VARIABELS(.*?)!!END VARIABELS\s*-->/s', $content, $matches)) {
            $block = trim($matches[1]);
            $lines = preg_split('/\r\n|\r|\n/', $block);

            foreach ($lines as $line) {
                if (preg_match('/(\w+):\s*(.+)/', trim($line), $kv)) {
                    $key = trim($kv[1]);
                    $path = trim($kv[2]);

                    if (strpos($path, 'this.') === 0) {
                        $path = substr($path, 5);
                    }

                    $value = $this->resolveDataPath($path, $data);
                    if ($value === null) {
                        error_log("Warning: could not resolve path '$path' for variable '$key'");
                    }

                    $variables[$key] = $value;
                }
            }

            $content = str_replace($matches[0], '', $content);
        }

        return [$content, $variables];
    }

    private function processLoops($content, $data, $variables = [])
    {
        return preg_replace_callback('/%%foreach (.*?) as (.*?)%%(.*?)%%endforeach%%/s', function ($matches) use ($data, $variables) {
            $arrayPath = trim($matches[1]);
            $itemVar = trim($matches[2]);
            $loopContent = $matches[3];

            if (strpos($arrayPath, 'this.') === 0) {
                $arrayPath = substr($arrayPath, 5);
            }
            else if (strpos($arrayPath, 'this') === 0) {
                $arrayPath = substr($arrayPath, 4);
            }

            $array = $this->resolveDataPath($arrayPath, $data);
            if (!is_array($array)) {
                return '';
            }

            $renderedLoop = '';
            $index = 0;
            foreach ($array as $item) {
            	$thisContent = $loopContent;
				$loopData = [$itemVar => $item, 'index' => $index, 'count' => count($array)];

                $thisContent = $this->processLoops($thisContent, $loopData, $variables);
                $thisContent = $this->resolveBuiltInFunctions($thisContent, $loopData,  $variables);
                $thisContent = $this->replaceTextVariables($thisContent, $loopData,  $variables);
                $thisContent = $this->processConditions($thisContent, $loopData,  $variables);

                $renderedLoop .= $thisContent;
                $index++;
            }

            return $renderedLoop;
        }, $content);
    }

    private function processConditions($content, $data, $variables = [])
    {
        return preg_replace_callback('/%%if \s*(.*?)%%(.*?)%%else%%(.*?)%%endif%%/s', function ($matches) use ($data, $variables) {
            $condition = trim($matches[1]);
            $trueContent = $matches[2];
            $falseContent = $matches[3];

            // Evaluate condition
            $conditionValue = $this->evaluateCondition($condition, $data, $variables);

            return $conditionValue ? $trueContent : $falseContent;
        }, $content);
    }

    private function replaceTextVariables($content, $context, $variables = [])
    {
        return preg_replace_callback('/{{\s*(.*?)\s*}}/', function ($matches) use ($context, $variables) {
            $dataPath = trim($matches[1]);
            $nullCoalescing = explode('??', $dataPath);
            if ($nullCoalescing && count($nullCoalescing) > 1) {
                $dataPath = trim($nullCoalescing[0]);
                $defaultValue = trim($nullCoalescing[1]);
            }
        
            $value = $this->tryGetValue($dataPath, $context, $variables);
            if (($value === null || empty($value)) && isset($defaultValue)) {
                if (preg_match('/^["\'].*["\']$/', $defaultValue)) {
                    $value = $defaultValue;
                } else {
                    $value = $this->tryGetValue($defaultValue, $context, $variables);
                }
            }

            return htmlspecialchars(is_scalar($value) ? $value : json_encode($value), ENT_QUOTES, 'UTF-8');
        }, $content);
    }

    private function tryGetValue($path, $context, $variables = []) {
        $segments = preg_split('/[\.\[\]]+/', $path, -1, PREG_SPLIT_NO_EMPTY);
        $first = $segments[0] ?? null;

        if ($first == 'this') {
            $path = implode('.', array_slice($segments, 1));
            return $this->resolveDataPath($path, $context);
        }
        elseif ($first && array_key_exists($first, $variables)) {
            return $this->resolveDataPath($path, $variables);
        }

        return $this->resolveDataPath($path, $context);
    }

    private function evaluateCondition($condition, $context)
    {
        // Replace data paths with their values in the condition
        $condition = preg_replace_callback('/{{\s*(.*?)\s*}}/', function ($matches) use ($context, $variables) {
            $key = trim($matches[1]);
            
            $value = array_key_exists($key, $variables) 
                ? $variables[$key] 
                : $this->resolveDataPath($key, $context);

            return is_string($value) ? "'" . addslashes($value) . "'" : $value;
        }, $condition);

        // Resolve built-in functions in conditions
        //$condition = $this->resolveBuiltInFunctions($condition);

        // Safe evaluation
        try {
            return eval('return ' . $condition . ';');
        } catch (Throwable $e) {
            return false;
        }
    }

    private function resolveDataPath($path, $context = null)
    {
        $keys = preg_split('/[\.\[\]]+/', $path, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($keys as $key) {
            if (is_array($context) && isset($context[$key])) {
                $context = $context[$key];
            } elseif (is_object($context) && isset($context->$key)) {
                $context = $context->$key;
            } else {
                return null;
            }
        }

        return $context;
    }

    private function resolveBuiltInFunctions($string, $context = [], $variables = [])
    {
        $builtInFunctions = $this->builtInFunctions;
        return preg_replace_callback('/{{\s*(' . implode('|', array_keys($this->builtInFunctions)) . ')\((.*?)\)\s*}}/', function ($matches) use ($builtInFunctions, $context, $variables) {
            $functionName = $matches[1];
            $rawArgs = array_map('trim', explode(',', $matches[2]));

            $resolvedArgs = array_map(function ($arg) use ($context, $variables) {
                if (is_numeric($arg)) {
                    return $arg + 0;
                }
                if (preg_match('/^["\'](.*)["\']$/', $arg, $m)) {
                    return $m[1];
                }
                
                $first = trim($arg);
                if (strpos($first, '.') !== false) {
                    $parts = explode('.', $first);
                    $first = $parts[0];
                }

                if ($first && array_key_exists($first, $variables)) {
                    return $this->resolveDataPath($arg, $variables);
                }
                
                if (strpos($arg, 'this.') === 0) {
                    $arg = substr($arg, 5);
                }
                elseif (strpos($arg, 'this') === 0) {
                    $arg = substr($arg, 4);
                }

                return $this->resolveDataPath($arg, $context);
            }, $rawArgs);

            return $this->builtInFunctions[$functionName]($resolvedArgs);
        }, $string);
    }

    private function renderElements($content)
    {
        return preg_replace_callback('/<element::([\w-]+) (.*?)\/>/', function ($matches) {
            $elementName = $matches[1];
            $elementPath = $this->basePath . 'elements/' . $elementName . '/index.html';
            if (!file_exists($elementPath)) {
                die("Element not found: $elementPath");
                error_log("Element not found: $elementPath");
                return '';
            }

            $elementContent = file_get_contents($elementPath);
            $elementContent = $this->replaceElementAttributes($elementContent, $matches[2]);

            return $elementContent;
        }, $content);
    }

    private function replaceElementAttributes($content, $attributesString)
    {
        preg_match_all('/(\w+)=["\'](.*?)["\']/', $attributesString, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $key = $match[1];
            $value = htmlspecialchars($match[2], ENT_QUOTES, 'UTF-8');
            $content = str_replace('{{ ' . $key . ' }}', $value, $content);
        }
        return $content;
    }


    private function initializeBuiltInFunctions(): void
    {
        $this->builtInFunctions = [
            'now' => function () { return date('Y-m-d H:i:s'); },
            'today' => function () { return date('Y-m-d'); },
            'time' => function () { return time(); },
            'date' => function ($args) { return date($args[0]); },
            'uppercase' => function ($args) { return strtoupper($args[0]); },
            'lowercase' => function ($args) { return strtolower($args[0]); },
            'capitalize' => function ($args) { return ucfirst($args[0]); },
            'trim' => function ($args) { return trim($args[0]); },
            'substr' => function ($args) { return substr($args[0], $args[1], $args[2] ?? null); },
            'rand' => function ($args) { return rand($args[0], $args[1]); },
            'count' => function ($args) { return count($args[0]); },
            'join' => function ($args) { return implode($args[1], $args[0]); },
            'default' => function ($args) { return !empty($args[0]) ? $args[0] : $args[1]; },
            'floor' => function ($args) { return floor($args[0]); },
            'ceil' => function ($args) { return ceil($args[0]); },
            'dateformat' => function($args) {
                $value = $args[0];
                $format = $args[1] ?? 'Y-m-d';
                if (!$value) return '';
                try {
                    $date = new DateTime($value);
                    return $date->format($format);
                } catch (Exception $e) {
                    return '';
                }
            },
        ];

        foreach (get_declared_classes() as $className) {
            if (in_array(\NSInternational\Functions\IFunction::class, class_implements($className))) {
                $service = new $className();
                $name = $service->getName();
    
                $this->builtInFunctions[$name] = function ($args) use ($service) {
                    return $service->execute($args);
                };
            }
        }
    }
}