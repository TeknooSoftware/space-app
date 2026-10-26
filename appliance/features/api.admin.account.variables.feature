@api @admin
Feature: API admin endpoints to administrate variables and secrets defined for accounts
  In order to keep the variables and secrets of the accounts out of the reach of the administration
  As an administrator of Space
  I can not read or change the variables and secrets of an account

  On Space, Job deployment can use variables in theirs configurations. Variables must be defined before each run, but
  they can be persisted on projects or centralized on the account to be share on all projects of the account.
  Variables can be a secret. According to the Space configuration secrets can be encrypted before be stored in the Space
  database and decrypted on the worker on the job execution.

  Background:
    Given a Space app instance
    And a memory document database

  Scenario: From the API, as Admin, create new account variable, via a request with a form url encoded body is refused
    Given an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to update account's variables as admin:
      | field                                     | value  |
      | account_vars.sets.0.envName               | prod   |
      | account_vars.sets.0.variables.0.id        |        |
      | account_vars.sets.0.variables.0.name      | var1   |
      | account_vars.sets.0.variables.0.wasSecret |        |
      | account_vars.sets.0.variables.0.value     | value1 |
      | account_vars.sets.0.variables.1.id        |        |
      | account_vars.sets.0.variables.1.name      | var2   |
      | account_vars.sets.0.variables.1.secret    | 1      |
      | account_vars.sets.0.variables.1.wasSecret |        |
      | account_vars.sets.0.variables.1.value     | value2 |
      | account_vars.sets.1.envName               | dev    |
      | account_vars.sets.1.variables.0.id        |        |
      | account_vars.sets.1.variables.0.name      | var3   |
      | account_vars.sets.1.variables.0.wasSecret |        |
      | account_vars.sets.1.variables.0.value     | value3 |
    Then get a JSON response
    But an 403 error

  Scenario: From the API, as Admin, get accounts variables ans secrets of my account is refused
    Given an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | eee | var5 | 0      | value5 | prod        |
      | fff | var6 | 1      | value6 | prod        |
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod        |
      | bbb | var2 | 1      | value2 | prod        |
      | ccc | var3 | 0      | value3 | prod        |
      | ddd | var4 | 0      | value4 | dev         |
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to get account's variables as admin
    Then get a JSON response
    But an 403 error
