(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.account = {
    attach: function attach(context, settings) {
      var progressBar = void 0;

      function updateCallback(progress, status, pb) {
        $('#updateprogress').html(progress + '%');
        if (progress === '100') {
          pb.stopMonitoring();
          window.setTimeout(function(){
            // Add a small delay to the redirect to allow the batch to fully
            // finish.
            window.location.href = "/drupal-batch-examples";
          }, 5000);
        }
      }

      function errorCallback(pb) {
        console.log(pb);
      }

      $.ajax({
        url: Drupal.url('drupal-batch-examples/batch-ajax-example-callback'),
        type: 'POST',
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        success: function success(value) {
          progressBar = new Drupal.ProgressBar('updateprogress', updateCallback, 'POST', errorCallback);
          progressBar.startMonitoring(value[0].data + '&op=do', 10);
        }
      });
    }
  };
})(jQuery, Drupal);
