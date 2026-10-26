@api @admin
Feature: API admin endpoints to create new job and deploy project
  In order to keep the deployments in the hands of the users of the accounts
  As an administrator of Space
  I can list, open and delete the jobs of a project, but I can not create, restart or follow a new job

  An administrator of Space is not a user of an account: it can not open a project, so it can not run a job on it.
  The users of the account run the jobs, the administration only supervises them.

  Background:
    Given a Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file

  Scenario: From the API, as Admin, execute a job from a project, via a request with a form url encoded body, is refused
    Given the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to create a new job as admin:
      | field                     | value                   |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
    Then get a JSON response
    But an 403 error
    And there are "0" jobs in the memory
    And no Kubernetes manifests have been created

  Scenario: From the API, as Admin, execute a job from a project, via a request with a json body, is refused
    Given the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to create a new job as admin with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON response
    But an 403 error
    And there are "0" jobs in the memory
    And no Kubernetes manifests have been created

  Scenario: From the API, as Admin, follow a pending job of a project is refused
    Given the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to get the pending status of the task "fooBar" as admin
    Then get a JSON response
    But an 403 error
