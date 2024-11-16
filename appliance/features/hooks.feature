Feature: On a space instance, we can define some hooks to build a project before the
  images creations. Currently Space supports Composer, Pip, Npm and make. But this
  hooks must be available only when path to these executables are defined in the DI

  Scenario: Get the Hook library with composer
    Given A Space app instance
    And without any hooks path defined
    And composer in several version as hook
    When the hook library is generated
    Then it obtains non empty hooks library with "behat-composer" key.

  Scenario: Get the Hook library without composer
    Given A Space app instance
    And without any hooks path defined
    When the hook library is generated
    Then it obtains an hooks library without "behat-composer" key.
