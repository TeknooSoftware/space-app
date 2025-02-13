Feature: API endpoints to manage variables and secrets usable in all deploymnets jobs of project
  In order to manage project's variables
  As an user of an account
  I want to manage variables and secrets available fo a project

  On Space, Job deployment can use variables in theirs configurations. Variables must be defined before each run, but
  they can be persisted on projects or centralized on the account to be share on all projects of the account.
  Variables can be a secret. According to the Space configuration secrets can be encrypted before be stored in the Space
  database and decrypted on the worker on the job execution.

  Scenario: From the API, get an owned project'variables
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get the last project's variables
    Then get a JSON reponse
    And the serialized "10" project's variables

  Scenario: From the API, get an non-owned project' variables and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get the last project's variables
    Then get a JSON reponse
    But an 403 error

  Scenario: From the API, edit an owned project's variables via a request with a form url encoded body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
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

  Scenario: From the API, edit an non-owned project's variables, via a request with a form url encoded body and get an
  error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
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
    But an 403 error

  Scenario: From the API, edit an owned project's variables via a request with a json body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
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
    And the serialized "2" project's variables with "DB_PWD" equals to "fooBar"

  Scenario: From the API, edit an non-owned project's variables, via a request with a json body and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
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
    But an 403 error

  Scenario: From the API, edit an owned project's variables with secrets encryptions, via a request with a form url
  encoded body
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
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
    And the serialized "2" project's variables with "DB_PWD" equals to "fooBar"

  Scenario: From the API, edit a non-owned project's variables with secrets encryptions, via a request with a form url
  encoded body and get an error
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
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
    But an 403 error

  Scenario: From the API, edit an owned project's variables with secrets encryptions, via a request with a json body
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
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
    And the serialized "2" project's variables with "DB_PWD" equals to "fooBar"

  Scenario: From the API, edit a non-owned project's variables with secrets encryptions, via a request with a json body
  and get an error
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
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
    But an 403 error
