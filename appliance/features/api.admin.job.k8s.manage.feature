@api @admin
Feature: API admin endpoints to list jobs and get informations about them
  In order to manage account's clusters
  As an administrator of Space
  I want to list launched jobs and get informations and history about them

  Job represent a project deployment. It can only created on a environments for an account. Job are immuable, and only
  histories created from workers. Users and administrators can only list and consult them.

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

  Scenario: From the API, as Admin, list jobs of a project
    Given "100" jobs for the project
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to list of jobs as admin
    Then get a JSON response
    And is a serialized collection of "100" items on "5" pages
    And the list of serialized jobs

  Scenario: From the API, as Admin, get a job from a project
    Given "1" jobs for the project
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to get the last job as admin
    Then get a JSON response
    And the serialized job

  Scenario: From the API, as Admin, delete a job from a project
    Given "1" jobs for the project
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to delete the last job as admin
    Then get a JSON response
    And the serialized deleted job
    And the job is deleted

  Scenario: From the API, as Admin, delete a job from a project, via a request with DELETE method
    Given "1" jobs for the project
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to delete the last job as admin with DELETE method
    Then get a JSON response
    And the serialized deleted job
    And the job is deleted
