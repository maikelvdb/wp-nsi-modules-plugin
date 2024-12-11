const intervalStates = [];

(function ($) {
  $(document).ready(function () {
    $(".js-date").each(function () {
      setDatePicker($(this));
    });

    $(".filter-input").each(async function () {
      const $select = $(this);
      const $input = $select.find("input[type='text']");
      const $codeInput = $select.find("input[type='hidden']");

      const code = $codeInput.val();
      if (code) {
        const name = await getStationByCode(code);
        $input.val(name);
      }

      $input.focus(renderStationsEvent);
      $input.keyup(renderStationsEvent);

      $input.blur(function () {
        const name = $input.data("name");
        delete intervalStates[name];

        setTimeout(() => {
          $(".nsi-options").remove();

          const $callbackWrapper = $(this).closest(".filter-input");
          const $wrapper = $(this).parent();
          const station = $(this).data("station");
          if (station) {
            $(this).val(station.name);
            $codeInput.val(station.beneCode);

            const callback = $callbackWrapper.data("callback");
            if (callback) {
              callback.call($callbackWrapper, station.beneCode);
            }
            return;
          }

          const val = $(this).val();
          const stations = $wrapper.data("stations");
          const found = stations.find(
            (s) => s.name === val || s.beneCode === val
          );

          if (found) {
            $codeInput.val(found.beneCode);
            const callback = $callbackWrapper.data("callback");
            if (callback) {
              callback.call($callbackWrapper, found.beneCode);
            }
          } else {
            $(this).val("");
            $codeInput.val("");
            $(this).data("station", undefined);
          }
        }, 200);
      });
    });

    $(".ns-form-switch").each(function () {
      const $this = $(this);
      const $fromInput = $("#from");
      const $fromCodeInput = $fromInput.next();
      const $toInput = $("#to");
      const $toCodeInput = $fromInput.next();

      $this.click(async function () {
        await new Promise((resolve) => setTimeout(resolve, 200));

        const from = $fromInput.val();
        const fromCode = $fromCodeInput.val();

        const to = $toInput.val();
        const toCode = $toCodeInput.val();

        $fromInput.val(to);
        $fromCodeInput.val(toCode);

        $toInput.val(from);
        $toCodeInput.val(fromCode);
      });
    });
  });

  function renderStationsEvent(event) {
    setTimeout(() => {
      if (event.type === "keyup") {
        $(this).data("station", undefined);
      }
      const $input = $(this);
      const name = $input.data("name");

      if (intervalStates[name]) {
        window.clearInterval(intervalStates[name]);
        delete intervalStates[name];
        return;
      }

      const value = $(this).val();
      intervalStates[name] = window.setTimeout(
        () => renderStationOptions(value, $(this).parent()),
        100
      );
    }, 300);
  }

  async function renderStationOptions(value, $wrapper) {
    const $input = $wrapper.find("input[type='text']");
    if (!$input.is(":focus")) {
      return;
    }

    let $list = $(".nsi-options");
    if ($list.length) {
      await new Promise((resolve) =>
        setTimeout(() => {
          $(".nsi-options").find("*").remove();
          resolve();
        }, 100)
      );
    } else {
      $list = $('<div class="nsi-options"></div>');
      const offset = $wrapper.offset();
      $list.css({
        top: offset.top + $wrapper.outerHeight() - 2,
        left: offset.left - 10,
        width: $wrapper.outerWidth() + 20,
      });

      $list.data("wrapper", $wrapper);
      $("body").append($list);
    }

    if (value.length < 3) {
      return;
    }

    const stations = await searchStations(value);
    $wrapper.data("stations", stations);

    const currentCode = $wrapper.find("input[type='hidden']").val();
    stations.forEach((station) => {
      const isActive = currentCode === station.beneCode;
      if (isActive) {
        // $input.data("station", station);
      }

      const $option = $(`
        <div class="option ${isActive ? "active" : ""}" data-value="${
        station.beneCode
      }" data-display="${station.name}" title="${station.name}">
          <span class="name">${station.name}</span>
        </div>
      `);

      $option.click(function (event) {
        event.preventDefault();

        const $currList = $(".nsi-options");
        $wrapper = $currList.data("wrapper");

        const $invalid = $wrapper.find("[invalid]");
        if ($invalid.length) {
          $invalid.removeAttr("invalid");
        }

        const $valueInput = $wrapper.find("input[type='text']");
        $valueInput.val(station.name);
        $valueInput.data("station", station);
      });

      $list.append($option);
    });
  }

  function setDatePicker($elem) {
    var $input = $elem.find("input");
    $input.datepicker({
      dateFormat: "dd-mm-yy",
      minDate: new Date(),
      onSelect: function (dateText, $this) {
        if (dateText) {
          $($this.input).removeAttr("invalid");
        }

        const callback = $this.input.data("callback");
        if (callback) {
          callback.call($this.input, dateText);
        }
      },
    });
  }
})(jQuery);
