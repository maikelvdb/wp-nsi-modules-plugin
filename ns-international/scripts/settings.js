jQuery(document).ready(async function ($) {
  const $search = $(".stations__search");
  if ($search.length === 0) {
    return;
  }

  const $input = $("input", $search);
  const $list = $(".stations_list");

  rerenderShortCode();

  $(".module_select").change(rerenderShortCode);

  $(".shortcode_preview").click(function () {
    const textElement = document.querySelector(".shortcode_preview");

    const copyText = document.createElement("input");
    copyText.value = textElement.innerHTML;

    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices

    navigator.clipboard.writeText(copyText.value);
    $(".ns-copy-confirm").fadeIn();

    setTimeout(() => {
      $(".ns-copy-confirm").fadeOut();
    }, 5000);
  });

  $(".js-from-station, .js-to-station").click(function () {
    $(this).removeAttr("data-code");
    rerenderShortCode();
  });

  $input.keyup(async function () {
    await triggerSearch($(this));
  });
  await triggerSearch($input);

  async function triggerSearch($elem) {
    const value = $elem.val().toLowerCase();
    if (value.length < 2) {
      return;
    }

    const stations = await searchStations(value);

    $list.empty();
    if (stations.length === 0) {
      $list.append(
        "<div class='row no-data'><div class='cell'>Geen resultaten</div></div>"
      );
      return;
    }

    stations.forEach((station) => {
      const $station = $(`
              <div class="row" data-name="${station.name}" data-code="${station.beneCode}">
                <div class="cell station__name">${station.name}</div>
                <div class="cell station__code">${station.beneCode}</div>
              </div>
            `);

      $station[0].addEventListener("contextmenu", (event) =>
        event.preventDefault()
      );

      $station.mousedown(function (event) {
        const mouseClickId = event.which;
        if (mouseClickId === 1) {
          const code = $(this).data("code");
          if ($(".js-from-station").attr("data-code") === code) {
            $(".js-from-station").removeAttr("data-code");
          } else {
            $(".js-from-station").attr("data-code", code);
          }

          if (
            $(".js-from-station").attr("data-code") ===
            $(".js-to-station").attr("data-code")
          ) {
            $(".js-to-station").removeAttr("data-code");
          }
        } else if (mouseClickId === 3) {
          const code = $(this).data("code");
          if ($(".js-to-station").attr("data-code") === code) {
            $(".js-to-station").removeAttr("data-code");
          } else {
            $(".js-to-station").attr("data-code", code);
          }

          if (
            $(".js-from-station").attr("data-code") ===
            $(".js-to-station").attr("data-code")
          ) {
            $(".js-from-station").removeAttr("data-code");
          }
        }

        rerenderShortCode();
      });
      $list.append($station);
    });
  }

  function rerenderShortCode() {
    const $shortCodePreview = $(".shortcode_preview");
    const module = $(".module_select").val();
    const from = $(".js-from-station").attr("data-code");
    const to = $(".js-to-station").attr("data-code");

    let shortCode = `[${module}`;
    if (from) {
      shortCode += ` from="${from}"`;
    }

    if (to) {
      shortCode += ` to="${to}"`;
    }

    $shortCodePreview.text(shortCode + " /]");
  }
});
