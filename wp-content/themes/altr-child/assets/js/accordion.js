class Accordion {
  constructor(container, { items = [] }) {
    if (
      !container ||
      !typeof container === "string" ||
      (!container.startsWith("#") && !container.startsWith("."))
    ) {
      throw new Error(
        "No has introducido un selector válido. Debe ser una cadena de texto y debe empezar por # o ."
      );
    }

    this.container = document.querySelector(container);

    for (const item of items) {
      if (!item.hasOwnProperty("title") || !item.hasOwnProperty("content")) {
        throw new Error(
          'Los elementos del accordion deben tener los atributos "title" y "content"'
        );
      }
    }

    this.items = items;

    this.initAccordion();
    this.initEventListeners();
  }

  initAccordion() {
    this.items.forEach((item) => {
      const title = item.title;
      const content = item.content;

      const h4Element = document.createElement("h4");
      const pElement = document.createElement("p");

      const accordionItem = document.createElement("div");
      accordionItem.classList.add("accordion-item");

      const accordionHeader = document.createElement("div");
      accordionHeader.classList.add("accordion-header");
      h4Element.innerHTML = title;
      accordionHeader.appendChild(h4Element);

      const accordionContent = document.createElement("div");
      accordionContent.classList.add("accordion-content");
      pElement.innerHTML = content;
      accordionContent.appendChild(pElement);

      accordionItem.appendChild(accordionHeader);
      accordionItem.appendChild(accordionContent);
      this.container.appendChild(accordionItem);
    });
  }

  initEventListeners() {
    const accordionItems = document.querySelectorAll(".accordion-item");
    const accordionContents = document.querySelectorAll(".accordion-content");
    const accordionHeaders = document.querySelectorAll(".accordion-header");

    accordionItems.forEach((item) => {
      const header = item.querySelector(".accordion-header");
      header.addEventListener("click", function () {
        const headerElement = this;
        accordionHeaders.forEach((accordionHeader) => {
          if (accordionHeader === headerElement) {
            accordionHeader.classList.toggle("active");
            return;
          }
          accordionHeader.classList.remove("active");
        });

        accordionContents.forEach((contentElement) => {
          const parent = contentElement.parentElement;

          if (parent === item) {
            parent.classList.toggle("show");

            if (parent.classList.contains("show")) {
              const contentElementChildren = contentElement.childNodes;

              let totalContentHeight = [...contentElementChildren].reduce(
                (totalHeight, element) => {
                  const elementStyles = window.getComputedStyle(element); // Obtiene todos los estilos aplicados al elemento
                  const marginTop = parseInt(elementStyles.marginTop);
                  const marginBottom = parseInt(elementStyles.marginBottom);
                  const paddingTop = parseInt(elementStyles.paddingTop);
                  const paddingBottom = parseInt(elementStyles.paddingBottom);
                  const height =
                    element.offsetHeight +
                    marginTop +
                    marginBottom +
                    paddingTop +
                    paddingBottom;

                  return totalHeight + height;
                },
                0
              );
              contentElement.style.maxHeight = totalContentHeight + "px";
              return;
            }
          } else {
            parent.classList.remove("show");
          }
          contentElement.removeAttribute("style");
        });
      });
    });
  }
}
