jQuery(document).ready(function ($) {
  $(".ns-international_searchform").each(function () {
    const $this = $(this);
    const $form = $this.find("form");
    $form.submit(function (event) {
      event.preventDefault();

      const $dateInput = $this.find('input[name="date"]');
      const $fromDisplay = $this.find('input[name="from_display"]');
      const $toDisplay = $this.find('input[name="to_display"]');

      $dateInput.attr("invalid", false);
      $fromDisplay.attr("invalid", false);
      $toDisplay.attr("invalid", false);

      let valid = true;
      const fromCode = $this.find('input[name="from"]').val();
      if (!fromCode) {
        $fromDisplay.attr("invalid", true);
        valid = false;
      }

      const toCode = $this.find('input[name="to"]').val();
      if (!toCode) {
        $toDisplay.attr("invalid", true);
        valid = false;
      }

      let date = $dateInput.val();
      if (!date) {
        $dateInput.attr("invalid", true);
        valid = false;
      }

      if (!valid) {
        return;
      }

      date = date.split("-").reverse().join("");
      const url = `${getBaseTrackingUrl()}${fromCode}%2F${toCode}%2F${date}`;

      window.open(url, "_blank");
    });
  });
});
