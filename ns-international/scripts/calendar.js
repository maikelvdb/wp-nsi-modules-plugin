jQuery(document).ready(function ($) {
  $(".ns-international_calendar").each(async function () {
    const $this = $(this);
    const from = $(this).data("from");
    const to = $(this).data("to");
    const minDate = $(this).data("min-date");

    const $next = $(this).find(".next");
    const $prev = $(this).find(".prev");
    const $container = $(this).find(".ns-calendar-container");
    const $calendars = $container.find(".ns-calendar");
    let maxIndex = -1;

    const slideCalendar = function () {
      const $slider = $container.find(".ns-calendar-slider");
      const current = $this.attr("data-current-index") ?? 0;
      const currentIndex = parseInt(current, 10);

      $slider[0].style.transform = `translateX(calc(${currentIndex * -100}% - ${
        currentIndex * 21
      }px))`;
    };

    $next.click(async function () {
      const currentIndex = $this.data("current-index");
      const newIndex = currentIndex + 1;
      $this.attr("data-current-index", newIndex);
      $this.data("current-index", newIndex);

      const dateName = $this
        .find('[data-index="' + newIndex + '"]')
        .data("date-str");

      $this.find(".js-active-date").text(dateName);

      if (newIndex == maxIndex) {
        $this.attr("is-last", true);
      }

      slideCalendar();
    });

    const currIndex = $this.attr("data-current-index");
    if (currIndex !== undefined && currIndex > 0) {
      slideCalendar();
    }

    $prev.click(async function () {
      const currentIndex = $this.data("current-index");
      const newIndex = currentIndex - 1;
      $this.attr("data-current-index", newIndex);
      $this.data("current-index", newIndex);

      const dateName = $this
        .find('[data-index="' + newIndex + '"]')
        .data("date-str");

      $this.find(".js-active-date").text(dateName);

      $this.removeAttr("is-last");

      slideCalendar();
    });
  });
});
