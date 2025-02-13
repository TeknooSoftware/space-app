Feature: Web interface to manage variables and secrets usable in all deploymnets jobs of account's projects
  In order to manage account's variables
  As an user of an account
  I want to manage variables and secrets available for all projects of my account

  On Space, Job deployment can use variables in theirs configurations. Variables must be defined before each run, but
  they can be persisted on projects or centralized on the account to be share on all projects of the account.
  Variables can be a secret. According to the Space configuration secrets can be encrypted before be stored in the Space
  database and decrypted on the worker on the job execution.

  Scenario: From the UI, create new account variables
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to account settings
    And open the account variables page
    Then it obtains a empty account's variables form
    When it submits the form:
      | field                                     | value  |
      | account_vars._token                       | <auto> |
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
    Then data have been saved
    Then the account must have these persisted variables
      | id | name | secret | value  | environment |
      | x  | var1 | 0      | value1 | prod        |
      | x  | var2 | 1      | value2 | prod        |
      | x  | var3 | 0      | value3 | dev         |
    And the user obtains the form:
      | field                                        | value  |
      | account_vars.sets.prod.envName               | prod   |
      | account_vars.sets.prod.variables.0.id        | x      |
      | account_vars.sets.prod.variables.0.name      | var1   |
      | account_vars.sets.prod.variables.0.secret    |        |
      | account_vars.sets.prod.variables.0.wasSecret |        |
      | account_vars.sets.prod.variables.0.value     | value1 |
      | account_vars.sets.prod.variables.1.id        | x      |
      | account_vars.sets.prod.variables.1.name      | var2   |
      | account_vars.sets.prod.variables.1.secret    | 1      |
      | account_vars.sets.prod.variables.1.wasSecret | 1      |
      | account_vars.sets.prod.variables.1.value     |        |
      | account_vars.sets.dev.envName                | dev    |
      | account_vars.sets.dev.variables.0.id         | x      |
      | account_vars.sets.dev.variables.0.name       | var3   |
      | account_vars.sets.dev.variables.0.secret     |        |
      | account_vars.sets.dev.variables.0.wasSecret  |        |
      | account_vars.sets.dev.variables.0.value      | value3 |

  Scenario: From the UI, update or delete account variables
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod        |
      | bbb | var2 | 1      | value2 | prod        |
      | ccc | var3 | 0      | value3 | prod        |
      | ddd | var4 | 0      | value4 | dev         |
      | eee | var6 | 1      | value6 | dev         |
      | fff | var8 | 1      | value8 | dev         |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to account settings
    And open the account variables page
    Then the user obtains the form:
      | field                                        | value  |
      | account_vars.sets.prod.envName               | prod   |
      | account_vars.sets.prod.variables.0.id        | aaa    |
      | account_vars.sets.prod.variables.0.name      | var1   |
      | account_vars.sets.prod.variables.0.secret    |        |
      | account_vars.sets.prod.variables.0.wasSecret |        |
      | account_vars.sets.prod.variables.0.value     | value1 |
      | account_vars.sets.prod.variables.1.id        | bbb    |
      | account_vars.sets.prod.variables.1.name      | var2   |
      | account_vars.sets.prod.variables.1.secret    | 1      |
      | account_vars.sets.prod.variables.1.wasSecret | 1      |
      | account_vars.sets.prod.variables.1.value     |        |
      | account_vars.sets.prod.variables.2.id        | ccc    |
      | account_vars.sets.prod.variables.2.name      | var3   |
      | account_vars.sets.prod.variables.2.secret    |        |
      | account_vars.sets.prod.variables.2.wasSecret |        |
      | account_vars.sets.prod.variables.2.value     | value3 |
      | account_vars.sets.dev.envName                | dev    |
      | account_vars.sets.dev.variables.0.id         | ddd    |
      | account_vars.sets.dev.variables.0.name       | var4   |
      | account_vars.sets.dev.variables.0.secret     |        |
      | account_vars.sets.dev.variables.0.wasSecret  |        |
      | account_vars.sets.dev.variables.0.value      | value4 |
      | account_vars.sets.dev.variables.1.id         | eee    |
      | account_vars.sets.dev.variables.1.name       | var6   |
      | account_vars.sets.dev.variables.1.secret     | 1      |
      | account_vars.sets.dev.variables.1.wasSecret  | 1      |
      | account_vars.sets.dev.variables.1.value      |        |
      | account_vars.sets.dev.variables.2.id         | fff    |
      | account_vars.sets.dev.variables.2.name       | var8   |
      | account_vars.sets.dev.variables.2.secret     | 1      |
      | account_vars.sets.dev.variables.2.wasSecret  | 1      |
      | account_vars.sets.dev.variables.2.value      |        |
    When it submits the form:
      | field                                        | value    |
      | account_vars._token                          | <auto>   |
      | account_vars.sets.prod.envName               | prod     |
      | account_vars.sets.prod.variables.1.id        | <auto>   |
      | account_vars.sets.prod.variables.1.name      | var2     |
      | account_vars.sets.prod.variables.1.wasSecret | 1        |
      | account_vars.sets.prod.variables.1.value     |          |
      | account_vars.sets.prod.variables.3.id        |          |
      | account_vars.sets.prod.variables.3.name      | var5     |
      | account_vars.sets.prod.variables.3.wasSecret |          |
      | account_vars.sets.prod.variables.3.value     | value5   |
      | account_vars.sets.dev.envName                | dev      |
      | account_vars.sets.dev.variables.0.name       | var3     |
      | account_vars.sets.dev.variables.0.id         | <auto>   |
      | account_vars.sets.dev.variables.0.wasSecret  |          |
      | account_vars.sets.dev.variables.0.value      | value3.1 |
      | account_vars.sets.dev.variables.1.id         | eee      |
      | account_vars.sets.dev.variables.1.name       | var6     |
      | account_vars.sets.dev.variables.1.secret     | 1        |
      | account_vars.sets.dev.variables.1.wasSecret  | 1        |
      | account_vars.sets.dev.variables.1.value      |          |
      | account_vars.sets.dev.variables.2.name       | var8     |
      | account_vars.sets.dev.variables.2.id         | <auto>   |
      | account_vars.sets.dev.variables.2.secret     |          |
      | account_vars.sets.dev.variables.2.wasSecret  | 1        |
      | account_vars.sets.dev.variables.2.value      | value8   |
    Then data have been saved
    Then the account must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 1      | value2   | prod        |
      | ddd | var3 | 0      | value3.1 | dev         |
      | eee | var6 | 1      | value6   | dev         |
      | fff | var8 | 0      | value8   | dev         |
      | x   | var5 | 0      | value5   | prod        |
    And the user obtains the form:
      | field                                        | value    |
      | account_vars.sets.prod.envName               | prod     |
      | account_vars.sets.prod.variables.0.id        | bbb      |
      | account_vars.sets.prod.variables.0.name      | var2     |
      | account_vars.sets.prod.variables.0.secret    | 1        |
      | account_vars.sets.prod.variables.0.wasSecret | 1        |
      | account_vars.sets.prod.variables.0.value     |          |
      | account_vars.sets.prod.variables.1.id        | x        |
      | account_vars.sets.prod.variables.1.name      | var5     |
      | account_vars.sets.prod.variables.1.secret    |          |
      | account_vars.sets.prod.variables.1.wasSecret |          |
      | account_vars.sets.prod.variables.1.value     | value5   |
      | account_vars.sets.dev.envName                | dev      |
      | account_vars.sets.dev.variables.0.name       | var3     |
      | account_vars.sets.dev.variables.0.id         | ddd      |
      | account_vars.sets.dev.variables.0.secret     |          |
      | account_vars.sets.dev.variables.0.wasSecret  |          |
      | account_vars.sets.dev.variables.0.value      | value3.1 |

  Scenario: From the UI, create new encrypted account secrets
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to account settings
    And open the account variables page
    Then it obtains a empty account's variables form
    When it submits the form:
      | field                                     | value  |
      | account_vars._token                       | <auto> |
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
    Then data have been saved
    Then the account must have these persisted variables
      | id | name | secret | value  | environment |
      | x  | var1 | 0      | value1 | prod        |
      | x  | var2 | 1      | value2 | prod        |
      | x  | var3 | 0      | value3 | dev         |
    And the user obtains the form:
      | field                                        | value  |
      | account_vars.sets.prod.envName               | prod   |
      | account_vars.sets.prod.variables.0.id        | x      |
      | account_vars.sets.prod.variables.0.name      | var1   |
      | account_vars.sets.prod.variables.0.secret    |        |
      | account_vars.sets.prod.variables.0.wasSecret |        |
      | account_vars.sets.prod.variables.0.value     | value1 |
      | account_vars.sets.prod.variables.1.id        | x      |
      | account_vars.sets.prod.variables.1.name      | var2   |
      | account_vars.sets.prod.variables.1.secret    | 1      |
      | account_vars.sets.prod.variables.1.wasSecret | 1      |
      | account_vars.sets.prod.variables.1.value     |        |
      | account_vars.sets.dev.envName                | dev    |
      | account_vars.sets.dev.variables.0.id         | x      |
      | account_vars.sets.dev.variables.0.name       | var3   |
      | account_vars.sets.dev.variables.0.secret     |        |
      | account_vars.sets.dev.variables.0.wasSecret  |        |
      | account_vars.sets.dev.variables.0.value      | value3 |

  Scenario: From the UI, update or delete encrypted account secrets
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod        |
      | bbb | var2 | 1      | value2 | prod        |
      | ccc | var3 | 0      | value3 | prod        |
      | ddd | var4 | 0      | value4 | dev         |
      | eee | var6 | 1      | value6 | dev         |
      | fff | var8 | 1      | value8 | dev         |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to account settings
    And open the account variables page
    Then the user obtains the form:
      | field                                        | value  |
      | account_vars.sets.prod.envName               | prod   |
      | account_vars.sets.prod.variables.0.id        | aaa    |
      | account_vars.sets.prod.variables.0.name      | var1   |
      | account_vars.sets.prod.variables.0.secret    |        |
      | account_vars.sets.prod.variables.0.wasSecret |        |
      | account_vars.sets.prod.variables.0.value     | value1 |
      | account_vars.sets.prod.variables.1.id        | bbb    |
      | account_vars.sets.prod.variables.1.name      | var2   |
      | account_vars.sets.prod.variables.1.secret    | 1      |
      | account_vars.sets.prod.variables.1.wasSecret | 1      |
      | account_vars.sets.prod.variables.1.value     |        |
      | account_vars.sets.prod.variables.2.id        | ccc    |
      | account_vars.sets.prod.variables.2.name      | var3   |
      | account_vars.sets.prod.variables.2.secret    |        |
      | account_vars.sets.prod.variables.2.wasSecret |        |
      | account_vars.sets.prod.variables.2.value     | value3 |
      | account_vars.sets.dev.envName                | dev    |
      | account_vars.sets.dev.variables.0.id         | ddd    |
      | account_vars.sets.dev.variables.0.name       | var4   |
      | account_vars.sets.dev.variables.0.secret     |        |
      | account_vars.sets.dev.variables.0.wasSecret  |        |
      | account_vars.sets.dev.variables.0.value      | value4 |
      | account_vars.sets.dev.variables.1.id         | eee    |
      | account_vars.sets.dev.variables.1.name       | var6   |
      | account_vars.sets.dev.variables.1.secret     | 1      |
      | account_vars.sets.dev.variables.1.wasSecret  | 1      |
      | account_vars.sets.dev.variables.1.value      |        |
      | account_vars.sets.dev.variables.2.id         | fff    |
      | account_vars.sets.dev.variables.2.name       | var8   |
      | account_vars.sets.dev.variables.2.secret     | 1      |
      | account_vars.sets.dev.variables.2.wasSecret  | 1      |
      | account_vars.sets.dev.variables.2.value      |        |
    When it submits the form:
      | field                                        | value    |
      | account_vars._token                          | <auto>   |
      | account_vars.sets.prod.envName               | prod     |
      | account_vars.sets.prod.variables.1.id        | <auto>   |
      | account_vars.sets.prod.variables.1.name      | var2     |
      | account_vars.sets.prod.variables.1.wasSecret | 1        |
      | account_vars.sets.prod.variables.1.value     |          |
      | account_vars.sets.prod.variables.3.id        |          |
      | account_vars.sets.prod.variables.3.name      | var5     |
      | account_vars.sets.prod.variables.3.wasSecret |          |
      | account_vars.sets.prod.variables.3.value     | value5   |
      | account_vars.sets.dev.envName                | dev      |
      | account_vars.sets.dev.variables.0.name       | var3     |
      | account_vars.sets.dev.variables.0.id         | <auto>   |
      | account_vars.sets.dev.variables.0.wasSecret  |          |
      | account_vars.sets.dev.variables.0.value      | value3.1 |
      | account_vars.sets.dev.variables.1.name       | var6     |
      | account_vars.sets.dev.variables.1.id         | <auto>   |
      | account_vars.sets.dev.variables.1.secret     | 1        |
      | account_vars.sets.dev.variables.1.wasSecret  | 1        |
      | account_vars.sets.dev.variables.1.value      | value7   |
      | account_vars.sets.dev.variables.2.name       | var8     |
      | account_vars.sets.dev.variables.2.id         | <auto>   |
      | account_vars.sets.dev.variables.2.secret     |          |
      | account_vars.sets.dev.variables.2.wasSecret  | 1        |
      | account_vars.sets.dev.variables.2.value      | value8   |
    Then data have been saved
    Then the account must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 1      | value2   | prod        |
      | ddd | var3 | 0      | value3.1 | dev         |
      | eee | var6 | 1      | value7   | dev         |
      | fff | var8 | 0      | value8   | dev         |
      | x   | var5 | 0      | value5   | prod        |
    And the user obtains the form:
      | field                                        | value    |
      | account_vars.sets.prod.envName               | prod     |
      | account_vars.sets.prod.variables.0.id        | bbb      |
      | account_vars.sets.prod.variables.0.name      | var2     |
      | account_vars.sets.prod.variables.0.secret    | 1        |
      | account_vars.sets.prod.variables.0.wasSecret | 1        |
      | account_vars.sets.prod.variables.0.value     |          |
      | account_vars.sets.prod.variables.1.id        | x        |
      | account_vars.sets.prod.variables.1.name      | var5     |
      | account_vars.sets.prod.variables.1.secret    |          |
      | account_vars.sets.prod.variables.1.wasSecret |          |
      | account_vars.sets.prod.variables.1.value     | value5   |
      | account_vars.sets.dev.envName                | dev      |
      | account_vars.sets.dev.variables.0.name       | var3     |
      | account_vars.sets.dev.variables.0.id         | ddd      |
      | account_vars.sets.dev.variables.0.secret     |          |
      | account_vars.sets.dev.variables.0.wasSecret  |          |
      | account_vars.sets.dev.variables.0.value      | value3.1 |
      | account_vars.sets.dev.variables.1.name       | var6     |
      | account_vars.sets.dev.variables.1.id         | eee      |
      | account_vars.sets.dev.variables.1.secret     | 1        |
      | account_vars.sets.dev.variables.1.wasSecret  | 1        |
      | account_vars.sets.dev.variables.1.value      |          |
