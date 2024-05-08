function addSubForm(collectionHolder) {
  // Get the data-prototype explained earlier
  let prototype = collectionHolder.dataset.prototype;
  let prototypeName = collectionHolder.dataset.prototypeName || '__name__';

  // get the new index
  let index = collectionHolder.dataset.index;

  let newForm = prototype;
  newForm = newForm.replace(new RegExp(prototypeName, 'g'), index);

  // increase the index with one for the next item
  collectionHolder.dataset.index = parseInt(collectionHolder.dataset.index) + 1;

  let dom = new DOMParser().parseFromString(newForm, 'text/html').body;
  while (dom.hasChildNodes()) collectionHolder.appendChild(dom.firstChild);
}

document.addEventListener("DOMContentLoaded",function () {
  document.addEventListener('click',function (event) {
    let button = event.target.closest('button');
    if (!button) {
      return false;
    }

    if (button.classList.contains('btn-add-subform')) {
      // prevent the link from creating a "#" on the URL
      event.preventDefault();

      // Get the ul that holds the subforms list
      let collectionHolder = button.closest('.subforms[data-subforms="' + button.dataset.subformsId + '"]');
      if (null === collectionHolder) {
        collectionHolder = document.querySelector('.subforms[data-subforms="' + button.dataset.subformsId + '"]');
      }
      collectionHolder.dataset.index = collectionHolder.querySelectorAll('.subform').length;

      // add a new sub form
      addSubForm(collectionHolder);

      return false;
    }

    if (button.classList.contains('action-remove')) {
      // prevent the link from creating a "#" on the URL
      event.preventDefault();
      event.target.closest('.subform').remove();

      return false;
    }

    if (button.classList.contains('btn-select-managed-cluster')) {
      // prevent the link from creating a "#" on the URL
      event.preventDefault();

      let clusterName = button.dataset.cluster;
      let envName = button.dataset.env;

      let form = event.target.closest('form');
      form.querySelector('input[type="hidden"].add-cluster-name').value = clusterName;
      form.querySelector('input[type="hidden"].add-env-name').value = envName;

      form.submit();
    }

    return false;
  });

  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('change', function () {
      form.querySelectorAll('.disableable-on-update').forEach(function (button) {
        if (button.hasAttribute('href')) {
          button.setAttribute('href', '#');
          button.addEventListener('click', function (event) {
            event.preventDefault();
            alert(button.dataset.disabledMessage);

            return false;
          });
        }
      });
    });
  });
});