Feature: On space, users on a some variables and secrets on a project to reuse them for new jobs,
  like certificates. This variables are persisted and injected for each new job.
  A job can redefine these variable, without erase variables in project.

  Scenario: Create new variables
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    And open the project variables page
    Then it obtains a empty project's variables form
    When it submits the form:
      | field                                     | value      |
      | project_vars._token                       | <auto>     |
      | project_vars.sets.0.environmentName       | prod       |
      | project_vars.sets.0.variables.0.id        |            |
      | project_vars.sets.0.variables.0.name      | var1       |
      | project_vars.sets.0.variables.0.wasSecret |            |
      | project_vars.sets.0.variables.0.value     | value1     |
      | project_vars.sets.0.variables.1.id        |            |
      | project_vars.sets.0.variables.1.name      | var2       |
      | project_vars.sets.0.variables.1.secret    | 1          |
      | project_vars.sets.0.variables.1.wasSecret |            |
      | project_vars.sets.0.variables.1.value     | value2     |
      | project_vars.sets.1.environmentName       | dev        |
      | project_vars.sets.1.variables.0.id        |            |
      | project_vars.sets.1.variables.0.name      | var3       |
      | project_vars.sets.1.variables.0.wasSecret |            |
      | project_vars.sets.1.variables.0.value     | value3     |
    Then the project must have these persisted variables
      | id | name | secret | value  | environment |
      | x  | var1 | 0      | value1 | prod        |
      | x  | var2 | 1      | value2 | prod        |
      | x  | var3 | 0      | value3 | dev         |
    And the user obtains the form:
      | field                                        | value      |
      | project_vars.sets.prod.environmentName       | prod       |
      | project_vars.sets.prod.variables.0.id        | x          |
      | project_vars.sets.prod.variables.0.name      | var1       |
      | project_vars.sets.prod.variables.0.secret    |            |
      | project_vars.sets.prod.variables.0.wasSecret |            |
      | project_vars.sets.prod.variables.0.value     | value1     |
      | project_vars.sets.prod.variables.1.id        | x          |
      | project_vars.sets.prod.variables.1.name      | var2       |
      | project_vars.sets.prod.variables.1.secret    | 1          |
      | project_vars.sets.prod.variables.1.wasSecret | 1          |
      | project_vars.sets.prod.variables.1.value     |            |
      | project_vars.sets.dev.environmentName        | dev        |
      | project_vars.sets.dev.variables.0.id         | x          |
      | project_vars.sets.dev.variables.0.name       | var3       |
      | project_vars.sets.dev.variables.0.secret     |            |
      | project_vars.sets.dev.variables.0.wasSecret  |            |
      | project_vars.sets.dev.variables.0.value      | value3     |

  Scenario: Update or delete variables
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project"
    And the project have these persisted variables:
      | id   | name | secret | value  | environment |
      | aaa  | var1 | 0      | value1 | prod        |
      | bbb  | var2 | 1      | value2 | prod        |
      | ccc  | var3 | 0      | value3 | prod        |
      | ddd  | var4 | 0      | value4 | dev         |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    And open the project variables page
    Then the user obtains the form:
      | field                                        | value      |
      | project_vars.sets.prod.environmentName       | prod       |
      | project_vars.sets.prod.variables.0.id        | aaa        |
      | project_vars.sets.prod.variables.0.name      | var1       |
      | project_vars.sets.prod.variables.0.secret    |            |
      | project_vars.sets.prod.variables.0.wasSecret |            |
      | project_vars.sets.prod.variables.0.value     | value1     |
      | project_vars.sets.prod.variables.1.id        | bbb        |
      | project_vars.sets.prod.variables.1.name      | var2       |
      | project_vars.sets.prod.variables.1.secret    | 1          |
      | project_vars.sets.prod.variables.1.wasSecret | 1          |
      | project_vars.sets.prod.variables.1.value     |            |
      | project_vars.sets.prod.variables.2.id        | ccc        |
      | project_vars.sets.prod.variables.2.name      | var3       |
      | project_vars.sets.prod.variables.2.secret    |            |
      | project_vars.sets.prod.variables.2.wasSecret |            |
      | project_vars.sets.prod.variables.2.value     | value3     |
      | project_vars.sets.dev.environmentName        | dev        |
      | project_vars.sets.dev.variables.0.id         | ddd        |
      | project_vars.sets.dev.variables.0.name       | var4       |
      | project_vars.sets.dev.variables.0.secret     |            |
      | project_vars.sets.dev.variables.0.wasSecret  |            |
      | project_vars.sets.dev.variables.0.value      | value4     |
    When it submits the form:
      | field                                        | value      |
      | project_vars._token                          | <auto>     |
      | project_vars.sets.prod.environmentName       | prod       |
      | project_vars.sets.prod.variables.1.id        | <auto>     |
      | project_vars.sets.prod.variables.1.name      | var2       |
      | project_vars.sets.prod.variables.1.wasSecret |            |
      | project_vars.sets.prod.variables.1.value     | value2     |
      | project_vars.sets.prod.variables.3.id        |            |
      | project_vars.sets.prod.variables.3.name      | var5       |
      | project_vars.sets.prod.variables.3.wasSecret |            |
      | project_vars.sets.prod.variables.3.value     | value5     |
      | project_vars.sets.dev.environmentName        | dev        |
      | project_vars.sets.dev.variables.0.name       | var3       |
      | project_vars.sets.dev.variables.0.id         | <auto>     |
      | project_vars.sets.dev.variables.0.wasSecret  |            |
      | project_vars.sets.dev.variables.0.value      | value3.1   |
    Then the project must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 0      | value2   | prod        |
      | ddd | var3 | 0      | value3.1 | dev         |
      | x   | var5 | 0      | value5   | prod        |
    And the user obtains the form:
      | field                                        | value      |
      | project_vars.sets.prod.environmentName       | prod       |
      | project_vars.sets.prod.variables.0.id        | bbb        |
      | project_vars.sets.prod.variables.0.name      | var2       |
      | project_vars.sets.prod.variables.0.secret    |            |
      | project_vars.sets.prod.variables.0.wasSecret |            |
      | project_vars.sets.prod.variables.0.value     | value2     |
      | project_vars.sets.prod.variables.1.id        | x          |
      | project_vars.sets.prod.variables.1.name      | var5       |
      | project_vars.sets.prod.variables.1.secret    |            |
      | project_vars.sets.prod.variables.1.wasSecret |            |
      | project_vars.sets.prod.variables.1.value     | value5     |
      | project_vars.sets.dev.environmentName        | dev        |
      | project_vars.sets.dev.variables.0.name       | var3       |
      | project_vars.sets.dev.variables.0.id         | ddd        |
      | project_vars.sets.dev.variables.0.secret     |            |
      | project_vars.sets.dev.variables.0.wasSecret  |            |
      | project_vars.sets.dev.variables.0.value      | value3.1   |
