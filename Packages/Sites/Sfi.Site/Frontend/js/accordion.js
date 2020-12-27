(function () {
    "use strict";
    var accordions = document.querySelectorAll(".js-Accordion");
    accordions.forEach(function (accordion) {
        var items = accordion.querySelectorAll(".js-AccordionItem");
        items.forEach(function (currentItem) {
            currentItem
                .querySelector(".js-AccordionItem-header")
                .addEventListener("click", function () {
                    items.forEach(function (item) {
                        if (item !== currentItem) {
                            item.classList.remove("isActive");
                        }
                    });
                    currentItem.classList.toggle("isActive");
                });
        });
    });
})();
