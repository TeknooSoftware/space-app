Feature: On space, users on a same account can define some variables and secrets available
  for all projects, like certificates. This variables are persisted and injected for each new job.
  A project or a job can redefine these variable, without erase variables in accounts for others projects.

  Scenario: Create new account variables
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
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
      | account_vars.sets.0.environmentName       | prod1  |
      | account_vars.sets.0.variables.0.id        |        |
      | account_vars.sets.0.variables.0.name      | var1   |
      | account_vars.sets.0.variables.0.wasSecret |        |
      | account_vars.sets.0.variables.0.value     | value1 |
      | account_vars.sets.0.variables.1.id        |        |
      | account_vars.sets.0.variables.1.name      | var2   |
      | account_vars.sets.0.variables.1.secret    | 1      |
      | account_vars.sets.0.variables.1.wasSecret |        |
      | account_vars.sets.0.variables.1.value     | value2 |
      | account_vars.sets.1.environmentName       | prod2  |
      | account_vars.sets.1.variables.0.id        |        |
      | account_vars.sets.1.variables.0.name      | var3   |
      | account_vars.sets.1.variables.0.wasSecret |        |
      | account_vars.sets.1.variables.0.value     | value3 |
    Then the account must have these persisted variables
      | id | name | secret | value  | environment |
      | x  | var1 | 0      | value1 | prod1       |
      | x  | var2 | 1      | value2 | prod1       |
      | x  | var3 | 0      | value3 | prod2       |
    And the user obtains the form:
      | field                                         | value  |
      | account_vars.sets.prod1.environmentName       | prod1  |
      | account_vars.sets.prod1.variables.0.id        | x      |
      | account_vars.sets.prod1.variables.0.name      | var1   |
      | account_vars.sets.prod1.variables.0.secret    |        |
      | account_vars.sets.prod1.variables.0.wasSecret |        |
      | account_vars.sets.prod1.variables.0.value     | value1 |
      | account_vars.sets.prod1.variables.1.id        | x      |
      | account_vars.sets.prod1.variables.1.name      | var2   |
      | account_vars.sets.prod1.variables.1.secret    | 1      |
      | account_vars.sets.prod1.variables.1.wasSecret | 1      |
      | account_vars.sets.prod1.variables.1.value     |        |
      | account_vars.sets.prod2.environmentName       | prod2  |
      | account_vars.sets.prod2.variables.0.id        | x      |
      | account_vars.sets.prod2.variables.0.name      | var3   |
      | account_vars.sets.prod2.variables.0.secret    |        |
      | account_vars.sets.prod2.variables.0.wasSecret |        |
      | account_vars.sets.prod2.variables.0.value     | value3 |

  Scenario: Update or delete account variables
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the account have these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod1       |
      | bbb | var2 | 1      | value2 | prod1       |
      | ccc | var3 | 0      | value3 | prod1       |
      | ddd | var4 | 0      | value4 | prod2       |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to account settings
    And open the account variables page
    Then the user obtains the form:
      | field                                         | value  |
      | account_vars.sets.prod1.environmentName       | prod1  |
      | account_vars.sets.prod1.variables.0.id        | aaa    |
      | account_vars.sets.prod1.variables.0.name      | var1   |
      | account_vars.sets.prod1.variables.0.secret    |        |
      | account_vars.sets.prod1.variables.0.wasSecret |        |
      | account_vars.sets.prod1.variables.0.value     | value1 |
      | account_vars.sets.prod1.variables.1.id        | bbb    |
      | account_vars.sets.prod1.variables.1.name      | var2   |
      | account_vars.sets.prod1.variables.1.secret    | 1      |
      | account_vars.sets.prod1.variables.1.wasSecret | 1      |
      | account_vars.sets.prod1.variables.1.value     |        |
      | account_vars.sets.prod1.variables.2.id        | ccc    |
      | account_vars.sets.prod1.variables.2.name      | var3   |
      | account_vars.sets.prod1.variables.2.secret    |        |
      | account_vars.sets.prod1.variables.2.wasSecret |        |
      | account_vars.sets.prod1.variables.2.value     | value3 |
      | account_vars.sets.prod2.environmentName       | prod2  |
      | account_vars.sets.prod2.variables.0.id        | ddd    |
      | account_vars.sets.prod2.variables.0.name      | var4   |
      | account_vars.sets.prod2.variables.0.secret    |        |
      | account_vars.sets.prod2.variables.0.wasSecret |        |
      | account_vars.sets.prod2.variables.0.value     | value4 |
    When it submits the form:
      | field                                         | value    |
      | account_vars._token                           | <auto>   |
      | account_vars.sets.prod1.environmentName       | prod1    |
      | account_vars.sets.prod1.variables.1.id        | <auto>   |
      | account_vars.sets.prod1.variables.1.name      | var2     |
      | account_vars.sets.prod1.variables.1.wasSecret | 1        |
      | account_vars.sets.prod1.variables.1.value     |          |
      | account_vars.sets.prod1.variables.3.id        |          |
      | account_vars.sets.prod1.variables.3.name      | var5     |
      | account_vars.sets.prod1.variables.3.wasSecret |          |
      | account_vars.sets.prod1.variables.3.value     | value5   |
      | account_vars.sets.prod2.environmentName       | prod2    |
      | account_vars.sets.prod2.variables.0.name      | var3     |
      | account_vars.sets.prod2.variables.0.id        | <auto>   |
      | account_vars.sets.prod2.variables.0.wasSecret |          |
      | account_vars.sets.prod2.variables.0.value     | value3.1 |
    Then the account must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 0      | value2   | prod1       |
      | ddd | var3 | 0      | value3.1 | prod2       |
      | x   | var5 | 0      | value5   | prod1       |
    And the user obtains the form:
      | field                                         | value    |
      | account_vars.sets.prod1.environmentName       | prod1    |
      | account_vars.sets.prod1.variables.0.id        | bbb      |
      | account_vars.sets.prod1.variables.0.name      | var2     |
      | account_vars.sets.prod1.variables.0.secret    |          |
      | account_vars.sets.prod1.variables.0.wasSecret |          |
      | account_vars.sets.prod1.variables.0.value     | value2   |
      | account_vars.sets.prod1.variables.1.id        | x        |
      | account_vars.sets.prod1.variables.1.name      | var5     |
      | account_vars.sets.prod1.variables.1.secret    |          |
      | account_vars.sets.prod1.variables.1.wasSecret |          |
      | account_vars.sets.prod1.variables.1.value     | value5   |
      | account_vars.sets.prod2.environmentName       | prod2    |
      | account_vars.sets.prod2.variables.0.name      | var3     |
      | account_vars.sets.prod2.variables.0.id        | ddd      |
      | account_vars.sets.prod2.variables.0.secret    |          |
      | account_vars.sets.prod2.variables.0.wasSecret |          |
      | account_vars.sets.prod2.variables.0.value     | value3.1 |
