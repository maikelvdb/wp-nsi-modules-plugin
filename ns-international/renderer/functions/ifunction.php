<?php

namespace NSInternational\Functions;

interface IFunction {
    public function getName();
    public function execute(array $args);
}
