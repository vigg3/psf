jQuery(document).ready(function () {
  const accordionBtns = document.querySelectorAll('.faqEntity');
  accordionBtns.forEach((accordion) => {
    accordion.children[0].onclick = function () {
      this.classList.toggle('active');
      let content = this.nextElementSibling;
      let contentChild = content.children[0];

      if (content.style.height) {
        content.style.height = null;
        contentChild.style.opacity = null;
        contentChild.style.height = null;
        contentChild.style.top = '-10px';
      } else {
        let height = content.scrollHeight + 24;
        content.style.height = height + 'px';
        contentChild.style.opacity = 1;
        contentChild.style.height = 'auto';
        contentChild.style.top = '0px';
      }
    };
  });
});
