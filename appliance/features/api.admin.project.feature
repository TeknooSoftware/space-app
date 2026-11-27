@api @admin
Feature: API admin endpoints to administrate projects
  In order to supervise projects
  As an administrator of Space
  I want to list all registered projects, without opening, creating or deleting them.

  On a space instance, each allowed users can register a new project on its attached account. A project must hosted on
  a source repository like GIT. Currently Space support only GIT via https and ssh, with tls keys or access token.
  A project can be compiled and pushed to a private OCI registry dedicated to the account and deploy builded containers
  to a cluster / servers (only Kubernetes is currently supported) in a dedicated namespace reserved to the account's
  environment selected on the job deployment.
  The Project's configuration store only informations about get the project from a repository and clusters where deploy
  it, per environment. The project's configuration can store variables. All others informations are stored directly into
  the space.paas.yaml file available at the root of the source repostirory.

  Background:
    Given a Space app instance
    And a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user

  Scenario: From the API, as Admin, list all registered projects
    Given an account for "My First Company" with the account namespace "my-first-company"
    And a user, called "Albert" "Jean" with the "albert@teknoo.space" with the password "Test2@Test"
    And "5" standard projects "project X" and a prefix "a-prefix"
    And an account for "My Other Company" with the account namespace "my-other-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And "5" standard projects "other project X" and a prefix "other-prefix"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to list of projects as admin
    Then get a JSON response
    And is a serialized collection of "10" items on "1" pages
    And the list of serialized projects

  Scenario: From the API, as Admin, list all projects of the selected account
    Given an account for "My First Company" with the account namespace "my-first-company"
    And a user, called "Albert" "Jean" with the "albert@teknoo.space" with the password "Test2@Test"
    And "5" standard projects "project X" and a prefix "a-prefix"
    And an account for "My Other Company" with the account namespace "my-other-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And "5" standard projects "other project X" and a prefix "other-prefix"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to list of projects of last account as admin
    Then get a JSON response
    And is a serialized collection of "5" items on "1" pages
    And the list of serialized projects of last account
