Feature: On a space instance, eacch allowed users can register a new project on its
  shared account. A project is hosted on a git repository, space support https and ssh cloning,
  with tls keys or access token. A project can be compiled and pushed to a private OCI
  registry dedicated to the account and deploy containers to a Kubernetes cluster.

  Scenario: List projects of an account
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project"
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    Then the user obtains a project list:
      | Name       |
      | my project |

  Scenario: Create a new project
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to new project page
    Then it obtains a empty project's form
    When it submits the form:
      | field                                                      | value                           |
      | space_project._token                                       | <auto>                          |
      | space_project.project.name                                 | my project                      |
      | space_project.projectMetadata.projectUrl                   | https://my.project.demo         |
      | space_project.project.prefix                               | demo                            |
      | space_project.project.sourceRepository.pullUrl             | https://oauth:token@gitlab.demo |
      | space_project.project.sourceRepository.defaultBranch       | main                            |
      | space_project.project.sourceRepository.identity.name       | git                             |
      | space_project.project.sourceRepository.identity.privateKey |                                 |
      | space_project.project.imagesRegistry.apiUrl                | <auto>                          |
      | space_project.project.imagesRegistry.identity.username     | <auto>                          |
      | space_project.project.imagesRegistry.identity.password     | <auto>                          |
      | space_project.project.clusters.0.name                      | <auto>                          |
      | space_project.project.clusters.0.type                      | <auto>                          |
      | space_project.project.clusters.0.address                   | <auto>                          |
      | space_project.project.clusters.0.environment.name          | <auto>                          |
      | space_project.project.clusters.0.identity.caCertificate    | <auto>                          |
      | space_project.project.clusters.0.identity.token            | <auto>                          |
    Then the project must be persisted
    And the user obtains the form:
      | field                                                      | value                                 |
      | space_project.project.name                                 | my project                            |
      | space_project.projectMetadata.projectUrl                   | https://my.project.demo               |
      | space_project.project.prefix                               | demo                                  |
      | space_project.project.sourceRepository.pullUrl             | https://oauth:token@gitlab.demo       |
      | space_project.project.sourceRepository.defaultBranch       | main                                  |
      | space_project.project.sourceRepository.identity.name       | git                                   |
      | space_project.project.sourceRepository.identity.privateKey |                                       |
      | space_project.project.imagesRegistry.apiUrl                | my-company.registry.demo.teknoo.space |
      | space_project.project.imagesRegistry.identity.username     | my-company-registry                   |
      | space_project.project.imagesRegistry.identity.password     |                                       |
      | space_project.project.clusters.0.name                      | Demo Kube Cluster                     |
      | space_project.project.clusters.0.type                      | kubernetes                            |
      | space_project.project.clusters.0.address                   | https://kubernetes.localhost:12345    |
      | space_project.project.clusters.0.environment.name          | prod                                  |
      | space_project.project.clusters.0.identity.caCertificate    | -----BEGIN CERTIFICATE-----FooBar     |
      | space_project.project.clusters.0.identity.token            |                                       |

  Scenario: Update a project
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "demo"
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    Then the user obtains a project list:
      | Name       |
      | my project |
    When it opens the project page of "my project"
    Then the user obtains the form:
      | field                                                      | value                                 |
      | space_project.project.name                                 | my project                            |
      | space_project.projectMetadata.projectUrl                   | https://my.project.demo               |
      | space_project.project.prefix                               | demo                                  |
      | space_project.project.sourceRepository.pullUrl             | https://oauth:token@gitlab.demo       |
      | space_project.project.sourceRepository.defaultBranch       | main                                  |
      | space_project.project.sourceRepository.identity.name       | git                                   |
      | space_project.project.sourceRepository.identity.privateKey |                                       |
      | space_project.project.imagesRegistry.apiUrl                | my-company.registry.demo.teknoo.space |
      | space_project.project.imagesRegistry.identity.username     | my-company-registry                   |
      | space_project.project.imagesRegistry.identity.password     |                                       |
      | space_project.project.clusters.0.name                      | Demo Kube Cluster                     |
      | space_project.project.clusters.0.type                      | kubernetes                            |
      | space_project.project.clusters.0.address                   | https://kubernetes.localhost:12345    |
      | space_project.project.clusters.0.environment.name          | prod                                  |
      | space_project.project.clusters.0.identity.caCertificate    | -----BEGIN CERTIFICATE-----FooBar     |
      | space_project.project.clusters.0.identity.token            |                                       |
    When it submits the form:
      | field                                                      | value                          |
      | space_project._token                                       | <auto>                         |
      | space_project.project.name                                 | my project 2                   |
      | space_project.projectMetadata.projectUrl                   | https://my2.project.demo       |
      | space_project.project.prefix                               | demo                           |
      | space_project.project.sourceRepository.pullUrl             | https://oauth:tok2@gitlab.demo |
      | space_project.project.sourceRepository.defaultBranch       | main                           |
      | space_project.project.sourceRepository.identity.name       | git                            |
      | space_project.project.sourceRepository.identity.privateKey |                                |
      | space_project.project.imagesRegistry.apiUrl                | <auto>                         |
      | space_project.project.imagesRegistry.identity.username     | <auto>                         |
      | space_project.project.imagesRegistry.identity.password     | <auto>                         |
      | space_project.project.clusters.0.name                      | <auto>                         |
      | space_project.project.clusters.0.type                      | <auto>                         |
      | space_project.project.clusters.0.address                   | <auto>                         |
      | space_project.project.clusters.0.environment.name          | <auto>                         |
      | space_project.project.clusters.0.identity.caCertificate    | <auto>                         |
      | space_project.project.clusters.0.identity.token            | <auto>                         |
    Then the project must be updated
    And the user obtains the form:
      | field                                                      | value                                 |
      | space_project.project.name                                 | my project 2                          |
      | space_project.projectMetadata.projectUrl                   | https://my2.project.demo              |
      | space_project.project.prefix                               | demo                                  |
      | space_project.project.sourceRepository.pullUrl             | https://oauth:tok2@gitlab.demo        |
      | space_project.project.sourceRepository.defaultBranch       | main                                  |
      | space_project.project.sourceRepository.identity.name       | git                                   |
      | space_project.project.sourceRepository.identity.privateKey |                                       |
      | space_project.project.imagesRegistry.apiUrl                | my-company.registry.demo.teknoo.space |
      | space_project.project.imagesRegistry.identity.username     | my-company-registry                   |
      | space_project.project.imagesRegistry.identity.password     |                                       |
      | space_project.project.clusters.0.name                      | Demo Kube Cluster                     |
      | space_project.project.clusters.0.type                      | kubernetes                            |
      | space_project.project.clusters.0.address                   | https://kubernetes.localhost:12345    |
      | space_project.project.clusters.0.environment.name          | prod                                  |
      | space_project.project.clusters.0.identity.caCertificate    | -----BEGIN CERTIFICATE-----FooBar     |
      | space_project.project.clusters.0.identity.token            |                                       |

  Scenario: Open a project from another account
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project"
    And an account for "My Firm" with the account namespace "my-firm"
    And an user, called "Hanin" "Roger" with the "hanin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    When the user sign in with "hanin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to project page of "my project" of "My Company"
    Then the user must have a 403 error

  Scenario: Delete a project
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project"
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    Then the user obtains a project list:
      | Name       |
      | my project |
    When It goes to delete the project "my project" of "My Company"
    Then the user obtains a project list:
      | Name |

  Scenario: Delete a project from another account
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project"
    And an account for "My Firm" with the account namespace "my-firm"
    And an user, called "Hanin" "Roger" with the "hanin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    When the user sign in with "hanin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to delete the project "my project" of "My Company"
    Then the user must have a 403 error
    And the project is not deleted
