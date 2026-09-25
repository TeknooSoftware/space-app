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

// Hide the fields and the labels of a cluster not used by its type. Fields stay submitted: a locked cluster checks
// every value.
function refreshClusterSubForm(subform) {
  let typeSelect = subform.querySelector('.cluster-type select');
  if (null === typeSelect) {
    return;
  }

  let type = typeSelect.value;
  subform.querySelectorAll('.cluster-field').forEach(function (field) {
    field.classList.toggle('d-none', !field.classList.contains('cluster-field-for-' + type));
  });
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
      collectionHolder.querySelectorAll('.cluster-subform').forEach(refreshClusterSubForm);

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

  document.querySelectorAll('.cluster-subform').forEach(refreshClusterSubForm);

  document.addEventListener('change', function (event) {
    let typeRow = event.target.closest('.cluster-type');
    if (null === typeRow) {
      return;
    }

    refreshClusterSubForm(typeRow.closest('.cluster-subform'));
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