@api @admin
Feature: API admin endpoints to administrate variables and secrets defined for projects
  In order to keep the variables and secrets of the projects out of the reach of the administration
  As an administrator of Space
  I can not read or change the variables and secrets of a project

  On Space, Job deployment can use variables in theirs configurations. Variables must be defined before each run, but
  they can be persisted on projects or centralized on the account to be share on all projects of the account.
  Variables can be a secret. According to the Space configuration secrets can be encrypted before be stored in the Space
  database and decrypted on the worker on the job execution.

  Background:
    Given a Space app instance
    And a memory document database

  Scenario: From the API, as Admin, get a project'variables is refused
    Given an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to get the last project's variables as admin
    Then get a JSON response
    But an 403 error

  Scenario: From the API, as Admin, edit a project's variables via a request with a form url encoded body is refused
    Given an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to edit a project's variables:
      | field                                      | value            |
      | project_vars.sets.prod.envName             | prod             |
      | project_vars.sets.prod.variables.20.name   | DB_NAME          |
      | project_vars.sets.prod.variables.20.value  | space_project_db |
      | project_vars.sets.prod.variables.21.name   | DB_PWD           |
      | project_vars.sets.prod.variables.21.secret | 1                |
      | project_vars.sets.prod.variables.21.value  | fooBar           |
    Then get a JSON response
    But an 403 error
