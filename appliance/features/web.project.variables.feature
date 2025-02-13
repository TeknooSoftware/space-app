Feature: Web interface to manage variables and secrets usable in all deploymnets jobs of project
  In order to manage project's variables
  As an user of an account
  I want to manage variables and secrets available fo a project

  On Space, Job deployment can use variables in theirs configurations. Variables must be defined before each run, but
  they can be persisted on projects or centralized on the account to be share on all projects of the account.
  Variables can be a secret. According to the Space configuration secrets can be encrypted before be stored in the Space
  database and decrypted on the worker on the job execution.

  Scenario: From the UI, create new variables on my project
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard project "my project"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    And open the project variables page
    Then it obtains a empty project's variables form
    When it submits the form:
      | field                                     | value  |
      | project_vars._token                       | <auto> |
      | project_vars.sets.0.envName               | prod   |
      | project_vars.sets.0.variables.0.id        |        |
      | project_vars.sets.0.variables.0.name      | var1   |
      | project_vars.sets.0.variables.0.wasSecret |        |
      | project_vars.sets.0.variables.0.value     | value1 |
      | project_vars.sets.0.variables.1.id        |        |
      | project_vars.sets.0.variables.1.name      | var2   |
      | project_vars.sets.0.variables.1.secret    | 1      |
      | project_vars.sets.0.variables.1.wasSecret |        |
      | project_vars.sets.0.variables.1.value     | value2 |
      | project_vars.sets.1.envName               | dev    |
      | project_vars.sets.1.variables.0.id        |        |
      | project_vars.sets.1.variables.0.name      | var3   |
      | project_vars.sets.1.variables.0.wasSecret |        |
      | project_vars.sets.1.variables.0.value     | value3 |
    Then data have been saved
    Then the project must have these persisted variables
      | id | name | secret | value  | environment |
      | x  | var1 | 0      | value1 | prod        |
      | x  | var2 | 1      | value2 | prod        |
      | x  | var3 | 0      | value3 | dev         |
    And the user obtains the form:
      | field                                        | value  |
      | project_vars.sets.prod.envName               | prod   |
      | project_vars.sets.prod.variables.0.id        | x      |
      | project_vars.sets.prod.variables.0.name      | var1   |
      | project_vars.sets.prod.variables.0.secret    |        |
      | project_vars.sets.prod.variables.0.wasSecret |        |
      | project_vars.sets.prod.variables.0.value     | value1 |
      | project_vars.sets.prod.variables.1.id        | x      |
      | project_vars.sets.prod.variables.1.name      | var2   |
      | project_vars.sets.prod.variables.1.secret    | 1      |
      | project_vars.sets.prod.variables.1.wasSecret | 1      |
      | project_vars.sets.prod.variables.1.value     |        |
      | project_vars.sets.dev.envName                | dev    |
      | project_vars.sets.dev.variables.0.id         | x      |
      | project_vars.sets.dev.variables.0.name       | var3   |
      | project_vars.sets.dev.variables.0.secret     |        |
      | project_vars.sets.dev.variables.0.wasSecret  |        |
      | project_vars.sets.dev.variables.0.value      | value3 |

  Scenario: From the UI, update or delete variables on my project
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard project "my project"
    And the project has these persisted variables:
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
    And It goes to projects list page
    And it goes to project page of "my project"
    And open the project variables page
    Then the user obtains the form:
      | field                                        | value  |
      | project_vars.sets.prod.envName               | prod   |
      | project_vars.sets.prod.variables.0.id        | aaa    |
      | project_vars.sets.prod.variables.0.name      | var1   |
      | project_vars.sets.prod.variables.0.secret    |        |
      | project_vars.sets.prod.variables.0.wasSecret |        |
      | project_vars.sets.prod.variables.0.value     | value1 |
      | project_vars.sets.prod.variables.1.id        | bbb    |
      | project_vars.sets.prod.variables.1.name      | var2   |
      | project_vars.sets.prod.variables.1.secret    | 1      |
      | project_vars.sets.prod.variables.1.wasSecret | 1      |
      | project_vars.sets.prod.variables.1.value     |        |
      | project_vars.sets.prod.variables.2.id        | ccc    |
      | project_vars.sets.prod.variables.2.name      | var3   |
      | project_vars.sets.prod.variables.2.secret    |        |
      | project_vars.sets.prod.variables.2.wasSecret |        |
      | project_vars.sets.prod.variables.2.value     | value3 |
      | project_vars.sets.dev.envName                | dev    |
      | project_vars.sets.dev.variables.0.id         | ddd    |
      | project_vars.sets.dev.variables.0.name       | var4   |
      | project_vars.sets.dev.variables.0.secret     |        |
      | project_vars.sets.dev.variables.0.wasSecret  |        |
      | project_vars.sets.dev.variables.0.value      | value4 |
      | project_vars.sets.dev.variables.1.id         | eee    |
      | project_vars.sets.dev.variables.1.name       | var6   |
      | project_vars.sets.dev.variables.1.secret     | 1      |
      | project_vars.sets.dev.variables.1.wasSecret  | 1      |
      | project_vars.sets.dev.variables.1.value      |        |
      | project_vars.sets.dev.variables.2.id         | fff    |
      | project_vars.sets.dev.variables.2.name       | var8   |
      | project_vars.sets.dev.variables.2.secret     | 1      |
      | project_vars.sets.dev.variables.2.wasSecret  | 1      |
      | project_vars.sets.dev.variables.2.value      |        |
    When it submits the form:
      | field                                        | value    |
      | project_vars._token                          | <auto>   |
      | project_vars.sets.prod.envName               | prod     |
      | project_vars.sets.prod.variables.1.id        | <auto>   |
      | project_vars.sets.prod.variables.1.name      | var2     |
      | project_vars.sets.prod.variables.1.wasSecret | 1        |
      | project_vars.sets.prod.variables.1.value     |          |
      | project_vars.sets.prod.variables.3.id        |          |
      | project_vars.sets.prod.variables.3.name      | var5     |
      | project_vars.sets.prod.variables.3.wasSecret |          |
      | project_vars.sets.prod.variables.3.value     | value5   |
      | project_vars.sets.dev.envName                | dev      |
      | project_vars.sets.dev.variables.0.name       | var3     |
      | project_vars.sets.dev.variables.0.id         | <auto>   |
      | project_vars.sets.dev.variables.0.wasSecret  |          |
      | project_vars.sets.dev.variables.0.value      | value3.1 |
      | project_vars.sets.dev.variables.1.name       | var6     |
      | project_vars.sets.dev.variables.1.id         | <auto>   |
      | project_vars.sets.dev.variables.1.secret     | 1        |
      | project_vars.sets.dev.variables.1.wasSecret  | 1        |
      | project_vars.sets.dev.variables.1.value      | value7   |
      | project_vars.sets.dev.variables.2.name       | var8     |
      | project_vars.sets.dev.variables.2.id         | <auto>   |
      | project_vars.sets.dev.variables.2.secret     | 0        |
      | project_vars.sets.dev.variables.2.wasSecret  | 1        |
      | project_vars.sets.dev.variables.2.value      | value8   |
    Then data have been saved
    Then the project must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 1      | value2   | prod        |
      | ddd | var3 | 0      | value3.1 | dev         |
      | eee | var6 | 1      | value7   | dev         |
      | fff | var8 | 0      | value8   | dev         |
      | x   | var5 | 0      | value5   | prod        |
    And the user obtains the form:
      | field                                        | value    |
      | project_vars.sets.prod.envName               | prod     |
      | project_vars.sets.prod.variables.0.id        | bbb      |
      | project_vars.sets.prod.variables.0.name      | var2     |
      | project_vars.sets.prod.variables.0.secret    | 1        |
      | project_vars.sets.prod.variables.0.wasSecret | 1        |
      | project_vars.sets.prod.variables.0.value     |          |
      | project_vars.sets.prod.variables.1.id        | x        |
      | project_vars.sets.prod.variables.1.name      | var5     |
      | project_vars.sets.prod.variables.1.secret    |          |
      | project_vars.sets.prod.variables.1.wasSecret |          |
      | project_vars.sets.prod.variables.1.value     | value5   |
      | project_vars.sets.dev.envName                | dev      |
      | project_vars.sets.dev.variables.0.name       | var3     |
      | project_vars.sets.dev.variables.0.id         | ddd      |
      | project_vars.sets.dev.variables.0.secret     |          |
      | project_vars.sets.dev.variables.0.wasSecret  |          |
      | project_vars.sets.dev.variables.0.value      | value3.1 |

  Scenario: From the UI, create new encrypted secret on my project
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard project "my project"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    And open the project variables page
    Then it obtains a empty project's variables form
    When it submits the form:
      | field                                     | value  |
      | project_vars._token                       | <auto> |
      | project_vars.sets.0.envName               | prod   |
      | project_vars.sets.0.variables.0.id        |        |
      | project_vars.sets.0.variables.0.name      | var1   |
      | project_vars.sets.0.variables.0.wasSecret |        |
      | project_vars.sets.0.variables.0.value     | value1 |
      | project_vars.sets.0.variables.1.id        |        |
      | project_vars.sets.0.variables.1.name      | var2   |
      | project_vars.sets.0.variables.1.secret    | 1      |
      | project_vars.sets.0.variables.1.wasSecret |        |
      | project_vars.sets.0.variables.1.value     | value2 |
      | project_vars.sets.1.envName               | dev    |
      | project_vars.sets.1.variables.0.id        |        |
      | project_vars.sets.1.variables.0.name      | var3   |
      | project_vars.sets.1.variables.0.wasSecret |        |
      | project_vars.sets.1.variables.0.value     | value3 |
    Then data have been saved
    Then the project must have these persisted variables
      | id | name | secret | value  | environment |
      | x  | var1 | 0      | value1 | prod        |
      | x  | var2 | 1      | value2 | prod        |
      | x  | var3 | 0      | value3 | dev         |
    And the user obtains the form:
      | field                                        | value  |
      | project_vars.sets.prod.envName               | prod   |
      | project_vars.sets.prod.variables.0.id        | x      |
      | project_vars.sets.prod.variables.0.name      | var1   |
      | project_vars.sets.prod.variables.0.secret    |        |
      | project_vars.sets.prod.variables.0.wasSecret |        |
      | project_vars.sets.prod.variables.0.value     | value1 |
      | project_vars.sets.prod.variables.1.id        | x      |
      | project_vars.sets.prod.variables.1.name      | var2   |
      | project_vars.sets.prod.variables.1.secret    | 1      |
      | project_vars.sets.prod.variables.1.wasSecret | 1      |
      | project_vars.sets.prod.variables.1.value     |        |
      | project_vars.sets.dev.envName                | dev    |
      | project_vars.sets.dev.variables.0.id         | x      |
      | project_vars.sets.dev.variables.0.name       | var3   |
      | project_vars.sets.dev.variables.0.secret     |        |
      | project_vars.sets.dev.variables.0.wasSecret  |        |
      | project_vars.sets.dev.variables.0.value      | value3 |

  Scenario: From the UI, update or delete encrypted secrets on my project
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard project "my project"
    And the project has these persisted variables:
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
    And It goes to projects list page
    And it goes to project page of "my project"
    And open the project variables page
    Then the user obtains the form:
      | field                                        | value  |
      | project_vars.sets.prod.envName               | prod   |
      | project_vars.sets.prod.variables.0.id        | aaa    |
      | project_vars.sets.prod.variables.0.name      | var1   |
      | project_vars.sets.prod.variables.0.secret    |        |
      | project_vars.sets.prod.variables.0.wasSecret |        |
      | project_vars.sets.prod.variables.0.value     | value1 |
      | project_vars.sets.prod.variables.1.id        | bbb    |
      | project_vars.sets.prod.variables.1.name      | var2   |
      | project_vars.sets.prod.variables.1.secret    | 1      |
      | project_vars.sets.prod.variables.1.wasSecret | 1      |
      | project_vars.sets.prod.variables.1.value     |        |
      | project_vars.sets.prod.variables.2.id        | ccc    |
      | project_vars.sets.prod.variables.2.name      | var3   |
      | project_vars.sets.prod.variables.2.secret    |        |
      | project_vars.sets.prod.variables.2.wasSecret |        |
      | project_vars.sets.prod.variables.2.value     | value3 |
      | project_vars.sets.dev.envName                | dev    |
      | project_vars.sets.dev.variables.0.id         | ddd    |
      | project_vars.sets.dev.variables.0.name       | var4   |
      | project_vars.sets.dev.variables.0.secret     |        |
      | project_vars.sets.dev.variables.0.wasSecret  |        |
      | project_vars.sets.dev.variables.0.value      | value4 |
      | project_vars.sets.dev.variables.1.id         | eee    |
      | project_vars.sets.dev.variables.1.name       | var6   |
      | project_vars.sets.dev.variables.1.secret     | 1      |
      | project_vars.sets.dev.variables.1.wasSecret  | 1      |
      | project_vars.sets.dev.variables.1.value      |        |
      | project_vars.sets.dev.variables.2.id         | fff    |
      | project_vars.sets.dev.variables.2.name       | var8   |
      | project_vars.sets.dev.variables.2.secret     | 1      |
      | project_vars.sets.dev.variables.2.wasSecret  | 1      |
      | project_vars.sets.dev.variables.2.value      |        |
    When it submits the form:
      | field                                        | value    |
      | project_vars._token                          | <auto>   |
      | project_vars.sets.prod.envName               | prod     |
      | project_vars.sets.prod.variables.1.id        | <auto>   |
      | project_vars.sets.prod.variables.1.name      | var2     |
      | project_vars.sets.prod.variables.1.wasSecret | 1        |
      | project_vars.sets.prod.variables.1.value     |          |
      | project_vars.sets.prod.variables.3.id        |          |
      | project_vars.sets.prod.variables.3.name      | var5     |
      | project_vars.sets.prod.variables.3.wasSecret |          |
      | project_vars.sets.prod.variables.3.value     | value5   |
      | project_vars.sets.dev.envName                | dev      |
      | project_vars.sets.dev.variables.0.name       | var3     |
      | project_vars.sets.dev.variables.0.id         | <auto>   |
      | project_vars.sets.dev.variables.0.wasSecret  |          |
      | project_vars.sets.dev.variables.0.value      | value3.1 |
      | project_vars.sets.dev.variables.1.name       | var6     |
      | project_vars.sets.dev.variables.1.id         | <auto>   |
      | project_vars.sets.dev.variables.1.secret     | 1        |
      | project_vars.sets.dev.variables.1.wasSecret  | 1        |
      | project_vars.sets.dev.variables.1.value      | value7   |
      | project_vars.sets.dev.variables.2.name       | var8     |
      | project_vars.sets.dev.variables.2.id         | <auto>   |
      | project_vars.sets.dev.variables.2.secret     | 0        |
      | project_vars.sets.dev.variables.2.wasSecret  | 1        |
      | project_vars.sets.dev.variables.2.value      | value8   |
    Then data have been saved
    Then the project must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 1      | value2   | prod        |
      | ddd | var3 | 0      | value3.1 | dev         |
      | eee | var6 | 1      | value7   | dev         |
      | fff | var8 | 0      | value8   | dev         |
      | x   | var5 | 0      | value5   | prod        |
    And the user obtains the form:
      | field                                        | value    |
      | project_vars.sets.prod.envName               | prod     |
      | project_vars.sets.prod.variables.0.id        | bbb      |
      | project_vars.sets.prod.variables.0.name      | var2     |
      | project_vars.sets.prod.variables.0.secret    | 1        |
      | project_vars.sets.prod.variables.0.wasSecret | 1        |
      | project_vars.sets.prod.variables.0.value     |          |
      | project_vars.sets.prod.variables.1.id        | x        |
      | project_vars.sets.prod.variables.1.name      | var5     |
      | project_vars.sets.prod.variables.1.secret    |          |
      | project_vars.sets.prod.variables.1.wasSecret |          |
      | project_vars.sets.prod.variables.1.value     | value5   |
      | project_vars.sets.dev.envName                | dev      |
      | project_vars.sets.dev.variables.0.name       | var3     |
      | project_vars.sets.dev.variables.0.id         | ddd      |
      | project_vars.sets.dev.variables.0.secret     |          |
      | project_vars.sets.dev.variables.0.wasSecret  |          |
      | project_vars.sets.dev.variables.0.value      | value3.1 |
      | project_vars.sets.dev.variables.1.name       | var6     |
      | project_vars.sets.dev.variables.1.id         | eee      |
      | project_vars.sets.dev.variables.1.secret     | 1        |
      | project_vars.sets.dev.variables.1.wasSecret  | 1        |
      | project_vars.sets.dev.variables.1.value      |          |
      | project_vars.sets.dev.variables.2.name       | var8     |
      | project_vars.sets.dev.variables.2.id         | fff      |
      | project_vars.sets.dev.variables.2.secret     |          |
      | project_vars.sets.dev.variables.2.wasSecret  |          |
      | project_vars.sets.dev.variables.2.value      | value8   |
