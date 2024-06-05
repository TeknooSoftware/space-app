Feature: On a space instance, an API is available to manage projects and integrating it with any platform

  Scenario: List owned projects from the API
    Given A Space app instance
    And A memory document database
    And an account for "My First Company" with the account namespace "my-first-company"
    And an user, called "Albert" "Jean" with the "albert@teknoo.space" with the password "Test2@Test"
    And "5" standard websites projects "project X" and a prefix "a-prefix"
    And an account for "My Other Company" with the account namespace "my-other-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And "5" standard websites projects "other project X" and a prefix "other-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to list of projects
    Then get a JSON reponse
    And is a serialized collection of "5" items on "1" pages
    And the a list of serialized owned projects

  Scenario: Create a project from the API
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a project:
      | field                                                       | value                                 |
      | space_project.project.name                                  | Behats Test                           |
      | space_project.projectMetadata.projectUrl                    | https://behat.tests                   |
      | space_project.project.prefix                                | behat-test                            |
      | space_project.project.sourceRepository.pullUrl              | https://oauth:foo@gitlab.teknoo.space |
      | space_project.project.sourceRepository.defaultBranch        | master                                |
      | space_project.project.sourceRepository.identity.name        | git                                   |
      | space_project.project.sourceRepository.identity.privateKey  |                                       |
      | space_project.project.imagesRegistry.apiUrl                 | registry.teknoo.space                 |
      | space_project.project.imagesRegistry.identity.auth          |                                       |
      | space_project.project.imagesRegistry.identity.username      | teknoo-software                       |
      | space_project.project.imagesRegistry.identity.password      | azertyy                               |
      | space_project.project.clusters.0.name                       | Demo Kube Cluster                     |
      | space_project.project.clusters.0.type                       | kubernetes                            |
      | space_project.project.clusters.0.address                    | https://kubernetes.localhost:12345    |
      | space_project.project.clusters.0.environment.name           | prod                                  |
      | space_project.project.clusters.0.identity.caCertificate     | -----BEGIN CERTIFICATE-----           |
      | space_project.project.clusters.0.identity.clientCertificate |                                       |
      | space_project.project.clusters.0.identity.clientKey         |                                       |
      | space_project.project.clusters.0.identity.token             | fooBar                                |
    Then get a JSON reponse
    And the serialized created project "Behats Test"
    And there is a project in the memory for this account

  Scenario: Create a project from the API with a json body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a project with a json body:
      | field                                         | value                                 |
      | project.name                                  | Behats Test                           |
      | projectMetadata.projectUrl                    | https://behat.tests                   |
      | project.prefix                                | behat-test                            |
      | project.sourceRepository.pullUrl              | https://oauth:foo@gitlab.teknoo.space |
      | project.sourceRepository.defaultBranch        | master                                |
      | project.sourceRepository.identity.name        | git                                   |
      | project.sourceRepository.identity.privateKey  |                                       |
      | project.imagesRegistry.apiUrl                 | registry.teknoo.space                 |
      | project.imagesRegistry.identity.auth          |                                       |
      | project.imagesRegistry.identity.username      | teknoo-software                       |
      | project.imagesRegistry.identity.password      | azertyy                               |
      | project.clusters.0.name                       | Demo Kube Cluster                     |
      | project.clusters.0.type                       | kubernetes                            |
      | project.clusters.0.address                    | https://kubernetes.localhost:12345    |
      | project.clusters.0.environment.name           | prod                                  |
      | project.clusters.0.identity.caCertificate     | -----BEGIN CERTIFICATE-----           |
      | project.clusters.0.identity.clientCertificate |                                       |
      | project.clusters.0.identity.clientKey         |                                       |
      | project.clusters.0.identity.token             | fooBar                                |
    Then get a JSON reponse
    And the serialized created project "Behats Test"
    And there is a project in the memory for this account

  Scenario: Get an owned project from the API
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get the last project
    Then get a JSON reponse
    And the serialized project "my project"

  Scenario: Get an non-owned project from the API
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard website project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get the last project
    Then get a JSON reponse
    And an 403 error

  Scenario: Get an owned project'variables from the API
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
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

  Scenario: Get an non-owned project' variables from the API
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard website project "my project" and a prefix "a-prefix"
    And "10" project's variables
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get the last project's variables
    Then get a JSON reponse
    And an 403 error

  Scenario: Edit an owned project from the API
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to edit a project:
      | field                                                       | value                                 |
      | space_project.project.name                                  | Behats Test                           |
      | space_project.projectMetadata.projectUrl                    | https://behat.tests                   |
      | space_project.project.prefix                                | behat-test                            |
      | space_project.project.sourceRepository.pullUrl              | https://oauth:foo@gitlab.teknoo.space |
      | space_project.project.sourceRepository.defaultBranch        | master                                |
      | space_project.project.sourceRepository.identity.name        | git                                   |
      | space_project.project.sourceRepository.identity.privateKey  |                                       |
      | space_project.project.imagesRegistry.apiUrl                 | registry.teknoo.space                 |
      | space_project.project.imagesRegistry.identity.auth          |                                       |
      | space_project.project.imagesRegistry.identity.username      | teknoo-software                       |
      | space_project.project.imagesRegistry.identity.password      | azertyy                               |
      | space_project.project.clusters.0.name                       | Demo Kube Cluster                     |
      | space_project.project.clusters.0.type                       | kubernetes                            |
      | space_project.project.clusters.0.address                    | https://kubernetes.localhost:12345    |
      | space_project.project.clusters.0.environment.name           | prod                                  |
      | space_project.project.clusters.0.identity.caCertificate     | -----BEGIN CERTIFICATE-----FooBar     |
      | space_project.project.clusters.0.identity.clientCertificate |                                       |
      | space_project.project.clusters.0.identity.clientKey         |                                       |
      | space_project.project.clusters.0.identity.token             | aFakeToken                            |
    Then get a JSON reponse
    And the serialized updated project "Behats Test"

  Scenario: Edit an non-owned project from the API
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard website project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to edit a project:
      | field                                                       | value                                 |
      | space_project.project.name                                  | Behats Test                           |
      | space_project.projectMetadata.projectUrl                    | https://behat.tests                   |
      | space_project.project.prefix                                | behat-test                            |
      | space_project.project.sourceRepository.pullUrl              | https://oauth:foo@gitlab.teknoo.space |
      | space_project.project.sourceRepository.defaultBranch        | master                                |
      | space_project.project.sourceRepository.identity.name        | git                                   |
      | space_project.project.sourceRepository.identity.privateKey  |                                       |
      | space_project.project.imagesRegistry.apiUrl                 | registry.teknoo.space                 |
      | space_project.project.imagesRegistry.identity.auth          |                                       |
      | space_project.project.imagesRegistry.identity.username      | teknoo-software                       |
      | space_project.project.imagesRegistry.identity.password      | azertyy                               |
      | space_project.project.clusters.0.name                       | Demo Kube Cluster                     |
      | space_project.project.clusters.0.type                       | kubernetes                            |
      | space_project.project.clusters.0.address                    | https://kubernetes.localhost:12345    |
      | space_project.project.clusters.0.environment.name           | prod                                  |
      | space_project.project.clusters.0.identity.caCertificate     | -----BEGIN CERTIFICATE-----FooBar     |
      | space_project.project.clusters.0.identity.clientCertificate |                                       |
      | space_project.project.clusters.0.identity.clientKey         |                                       |
      | space_project.project.clusters.0.identity.token             | aFakeToken                            |
    Then get a JSON reponse
    And an 403 error

  Scenario: Edit an owned project from the API with a json body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to edit a project with a json body:
      | field                                         | value                                 |
      | project.name                                  | Behats Test                           |
      | projectMetadata.projectUrl                    | https://behat.tests                   |
      | project.prefix                                | behat-test                            |
      | project.sourceRepository.pullUrl              | https://oauth:foo@gitlab.teknoo.space |
      | project.sourceRepository.defaultBranch        | master                                |
      | project.sourceRepository.identity.name        | git                                   |
      | project.sourceRepository.identity.privateKey  |                                       |
      | project.imagesRegistry.apiUrl                 | registry.teknoo.space                 |
      | project.imagesRegistry.identity.auth          |                                       |
      | project.imagesRegistry.identity.username      | teknoo-software                       |
      | project.imagesRegistry.identity.password      | azertyy                               |
      | project.clusters.0.name                       | Demo Kube Cluster                     |
      | project.clusters.0.type                       | kubernetes                            |
      | project.clusters.0.address                    | https://kubernetes.localhost:12345    |
      | project.clusters.0.environment.name           | prod                                  |
      | project.clusters.0.identity.caCertificate     | -----BEGIN CERTIFICATE-----FooBar     |
      | project.clusters.0.identity.clientCertificate |                                       |
      | project.clusters.0.identity.clientKey         |                                       |
      | project.clusters.0.identity.token             | aFakeToken                            |
    Then get a JSON reponse
    And the serialized updated project "Behats Test"

  Scenario: Edit an non-owned project from the API with a json body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard website project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to edit a project with a json body:
      | field                                         | value                                 |
      | project.name                                  | Behats Test                           |
      | projectMetadata.projectUrl                    | https://behat.tests                   |
      | project.prefix                                | behat-test                            |
      | project.sourceRepository.pullUrl              | https://oauth:foo@gitlab.teknoo.space |
      | project.sourceRepository.defaultBranch        | master                                |
      | project.sourceRepository.identity.name        | git                                   |
      | project.sourceRepository.identity.privateKey  |                                       |
      | project.imagesRegistry.apiUrl                 | registry.teknoo.space                 |
      | project.imagesRegistry.identity.auth          |                                       |
      | project.imagesRegistry.identity.username      | teknoo-software                       |
      | project.imagesRegistry.identity.password      | azertyy                               |
      | project.clusters.0.name                       | Demo Kube Cluster                     |
      | project.clusters.0.type                       | kubernetes                            |
      | project.clusters.0.address                    | https://kubernetes.localhost:12345    |
      | project.clusters.0.environment.name           | prod                                  |
      | project.clusters.0.identity.caCertificate     | -----BEGIN CERTIFICATE-----FooBar     |
      | project.clusters.0.identity.clientCertificate |                                       |
      | project.clusters.0.identity.clientKey         |                                       |
      | project.clusters.0.identity.token             | aFakeToken                            |
    Then get a JSON reponse
    And an 403 error

  Scenario: Edit an owned project's variables from the API
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
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

  Scenario: Edit an non-owned project's project variables from the API
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard website project "my project" and a prefix "a-prefix"
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
    And an 403 error

  Scenario: Edit an owned project's variables from the API with a json body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
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

  Scenario: Edit an non-owned project's project variables from the API with a json body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard website project "my project" and a prefix "a-prefix"
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
    And an 403 error

  Scenario: Edit an owned project's variables from the API with secrets encryptions
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
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

  Scenario: Edit an non-owned project's project variables from the API with secrets encryptions
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard website project "my project" and a prefix "a-prefix"
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
    And an 403 error

  Scenario: Edit an owned project's variables from the API with a json body with secrets encryptions
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
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

  Scenario: Edit an non-owned project's project variables from the API with a json body with secrets encryptions
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard website project "my project" and a prefix "a-prefix"
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
    And an 403 error

  Scenario: Delete an owned project from the API
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to delete the last project
    Then get a JSON reponse
    And the serialized deleted project
    And the project is deleted

  Scenario: Delete an non-owned project from the API
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard website project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to delete the last project
    Then get a JSON reponse
    And an 403 error
    And the project is not deleted

  Scenario: Delete an owned project from the API with DELETE method
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to delete the last project with DELETE method
    Then get a JSON reponse
    And the serialized deleted project
    And the project is deleted

  Scenario: Delete an non-owned project from the API with DELETE method
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard website project "my project" and a prefix "a-prefix"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to delete the last project with DELETE method
    Then get a JSON reponse
    And an 403 error
    And the project is not deleted