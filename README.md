# Drupal Batch Examples

Compatible with Drupal 10 and 11.

This codebase contains a number of modules that show how to use the batch API
in different ways. The modules have been split into different parts to show
different parts of the batch API in action.

Every module has a form that can be submitted to initiate the batch process.

All modules have unit tests to test that the batch has run correctly.

## Drupal Batch Examples

A meta-module to provide a consistent navigation system for the other examples.

Path: `/drupal-batch-examples`

## Batch Form Example

An example of running a batch in a self contained form.

Path: `/drupal-batch-examples/batch-form-example`

## Batch Controller Example

An example of running a batch from a controller.

Path: `/drupal-batch-examples/batch-controller-example`

## Batch Class Example

An example of running batch via a class. Includes Drush support.

Path: `/drupal-batch-examples/batch-class-example`

This module also shows how to use the batch API in Drush. The same batch process
is run via the form and via the Drush command `drush batch_class_example:run`.

## Batch Finished Example

An example of using the finished property of the batch API.

Path: `/drupal-batch-examples/batch-finish-example`

An example of using the finished property to process nodes on the site.

Path: `/drupal-batch-examples/batch-process-nodes`

## Batch Addition Example

An example of adding a batch inside a running batch.

Path: `/drupal-batch-examples/batch-addition-example`

## Batch CSV Example

An example of using the batch API to process a CSV file.

This batch process will create nodes of the type "article", which comes with the
standard install profile. In this case we are taking a two column CSV and using
the first column for the title and the second column for the body content.

Path: `/drupal-batch-examples/batch-csv-example`

You can download the CSV needed for this form at the path
`/drupal-batch-examples/batch-csv-example/generate-csv`, which is also linked to
from the batch form itself. The CSV is generated from random data and so will
be different every time you download it.

## Batch Download Example

An example of how to generate a large file in a batch process and then issue
that file to the user.

Path: `/drupal-batch-examples/batch-download-example`

This example works by setting a redirecting the user back to a controller page
once the file has been created. This then issues the file as a download and
deletes it at the same time.

## Batch Ajax Example

Shows an example of running a batch process outside of the normal batch
interface.

Path: `/drupal-batch-examples/batch-ajax-example`

You can use this example to create a different page for the user that isn't
part of the normal batch systems, but still runs a normal batch process. You
can use this as an interstitial page for when you want to prepare things for
your users.

# Thanks

- Some of the batch process and finishing code is taken from
https://www.drupalatyourfingertips.com/bq.
- The SVG used in the Ajax example is by
https://github.com/SamHerbert/SVG-Loaders.
