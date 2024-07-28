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

## Batch Class Example

An example of running batch via a class. Includes Drush support.

Path: `/drupal-batch-examples/batch-class-example`

This module also shows how to use the batch API in Drush. The same batch process
is run via the form and via the Drush command `drush batch_class_example:run`.

## Batch Finish Example

An example of using the finish property of the batch API.

Path: `/drupal-batch-examples/batch-finish-example`

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
