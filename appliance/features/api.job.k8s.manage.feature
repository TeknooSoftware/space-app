@api
Feature: API endpoints to list jobs and get informations about them
  In order to manage account's clusters
  As a user of an account
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
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user

  Scenario: From the API, list jobs of an owned project
    Given a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And "100" jobs for the project
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to list of jobs
    Then get a JSON response
    And is a serialized collection of "100" items on "5" pages
    And the list of serialized jobs

  Scenario: From the API, list jobs of a non-owned project and get an error
    Given an account for "An Other Company" with the account namespace "my-company"
    And a user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And "100" jobs for the project
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to list of jobs
    Then get a JSON response
    But an 403 error

  Scenario: From the API, get a job from an owned project
    Given a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And "1" jobs for the project
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to get the last job
    Then get a JSON response
    And the serialized job

  Scenario: From the API, get a job from a non-owned project and get an error
    Given an account for "An Other Company" with the account namespace "my-company"
    And a user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And "1" jobs for the project
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to get the last job
    Then get a JSON response
    But an 403 error

  Scenario: From the API, delete a job from an owned project
    Given a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And "1" jobs for the project
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to delete the last job
    Then get a JSON response
    And the serialized deleted job
    And the job is deleted

  Scenario: From the API, delete a job from a non-owned project and get an error
    Given an account for "An Other Company" with the account namespace "my-company"
    And a user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And "1" jobs for the project
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to delete the last job
    Then get a JSON response
    But an 403 error
    And the job is not deleted

  Scenario: From the API, delete a job from an owned project, via a request with DELETE method
    Given a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And "1" jobs for the project
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to delete the last job with DELETE method
    Then get a JSON response
    And the serialized deleted job
    And the job is deleted

  Scenario: From the API, delete a job from a non-owned project, via a request with DELETE method and get an error
    Given an account for "An Other Company" with the account namespace "my-company"
    And a user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And "1" jobs for the project
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to delete the last job with DELETE method
    Then get a JSON response
    But an 403 error
    And the job is not deleted
