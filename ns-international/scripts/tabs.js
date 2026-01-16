jQuery(document).ready(function ($) {
  $(".tab-container").each(function () {
    const $container = $(this);
    const $tabs = $container.find(".tab");
    $tabs.each(function () {
      const id = $(this).data("tab");
      $(this).attr("href", `#${id}`);
    });

    $container.find(".tab-content").removeClass("active");

    const hash = window.location.hash;
    if (hash) {
      const $matchingTab = $tabs.filter(`[href="${hash}"]`);
      if ($matchingTab.length) {
        const id = $matchingTab.data("tab");
        updateTabContent($container, id);
      }
    } else {
      const $firstTab = $tabs.first();
      const firstTabId = $firstTab.data("tab");
      updateTabContent($container, firstTabId);
    }

    $tabs.on("click", function () {
      const id = $(this).data("tab");
      updateTabContent($container, id);
    });
  });

  function updateTabContent($container, id) {
    $container.find(".tab").removeClass("active");
    $container.find(`.tab[href="#${id}"]`).addClass("active");

    $container.find(".tab-content").removeClass("active");
    $container.find(`#${id}`).addClass("active");
  }
});
