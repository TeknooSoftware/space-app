@worker
Feature: Hooks library offered to the worker to build a project
  In order to build a project before its images are created
  As a worker running a job
  I want to only get the hooks whose executable path is defined in the DI

  On a space instance, hooks can be defined to build a project before its images are created. Space currently
  supports Composer, Pip, Npm and make, but a hook must only be available when the path to its executable is
  defined in the DI.

  Background:
    Given a Space app instance
    And without any hooks path defined

  Scenario: In a worker builder, get the Hook library with composer
    Given composer in several version as hook
    When the hook library is generated
    Then it obtains non empty hooks library with "behat-composer" key.

  Scenario: In a worker builder, get the Hook library without composer
    When the hook library is generated
    Then it obtains an hooks library without "behat-composer" key.
