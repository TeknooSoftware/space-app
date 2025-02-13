Feature: API admin endpoints to administrate variables and secrets defined for accounts
  In order to manage account's clusters
  As an administrator of Space
  I want to manage accounts variables and secrets of each registered account

  On Space, Job deployment can use variables in theirs configurations. Variables must be defined before each run, but
  they can be persisted on projects or centralized on the account to be share on all projects of the account.
  Variables can be a secret. According to the Space configuration secrets can be encrypted before be stored in the Space
  database and decrypted on the worker on the job execution.

  Scenario: From the API, as Admin, get an project'variables
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get the last project's variables as admin
    Then get a JSON reponse
    And the serialized "10" project's variables

  Scenario: From the API, as Admin, edit an project's variables via a request with a form url encoded body
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to edit a project's variables:
      | field                                      | value            |
      | project_vars.sets.prod.envName             | prod             |
      | project_vars.sets.prod.variables.20.name   | DB_NAME          |
      | project_vars.sets.prod.variables.20.value  | space_project_db |
      | project_vars.sets.prod.variables.21.name   | DB_PWD           |
      | project_vars.sets.prod.variables.21.secret | 1                |
      | project_vars.sets.prod.variables.21.value  | fooBar           |
    Then get a JSON reponse
    And the serialized "2" project's variables with "DB_NAME" equals to "space_project_db"

  Scenario: From the API, as Admin, edit an project's variables via a request with a json body
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to edit a project's variables with a json body:
      | field                        | value            |
      | sets.prod.envName            | prod             |
      | sets.prod.variables.0.name   | DB_NAME          |
      | sets.prod.variables.0.value  | space_project_db |
      | sets.prod.variables.1.name   | DB_PWD           |
      | sets.prod.variables.1.secret | 1                |
      | sets.prod.variables.1.value  | fooBar           |
    Then get a JSON reponse
    And the serialized "2" project's variables with "DB_NAME" equals to "space_project_db"

  Scenario: From the API, as Admin, edit an project's variables with secrets encryptions via a request with a form url
  encoded body
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to edit a project's variables:
      | field                                      | value            |
      | project_vars.sets.prod.envName             | prod             |
      | project_vars.sets.prod.variables.20.name   | DB_NAME          |
      | project_vars.sets.prod.variables.20.value  | space_project_db |
      | project_vars.sets.prod.variables.21.name   | DB_PWD           |
      | project_vars.sets.prod.variables.21.secret | 1                |
      | project_vars.sets.prod.variables.21.value  | fooBar           |
    Then get a JSON reponse
    And the serialized "2" project's variables with "DB_NAME" equals to "space_project_db"

  Scenario: From the API, as Admin, edit an project's variables with secrets encryptions, via a request with a json body
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to edit a project's variables with a json body:
      | field                        | value            |
      | sets.prod.envName            | prod             |
      | sets.prod.variables.0.name   | DB_NAME          |
      | sets.prod.variables.0.value  | space_project_db |
      | sets.prod.variables.1.name   | DB_PWD           |
      | sets.prod.variables.1.secret | 1                |
      | sets.prod.variables.1.value  | fooBar           |
    Then get a JSON reponse
    And the serialized "2" project's variables with "DB_NAME" equals to "space_project_db"
