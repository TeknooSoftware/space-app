@web
Feature: Web dashboard embedding the web dashboard of the selected cluster
  In order to follow my workloads from Space
  As a user of an account
  I want to see the web dashboard of the cluster hosting my environment, without signing in on it

  The dashboard page embeds, in an iframe, the web dashboard of the cluster hosting the selected environment. Space
  relays it and injects the environment's credentials, so the user never signs in on the dashboard. A cluster without
  a configured dashboard shows no iframe.

  Background:
    Given a Space app instance
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user

  Scenario: From the UI, the dashboard of the environment's cluster is embedded
    Given the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It goes to the dashboard of "Demo Kube Cluster~dev"
    Then the dashboard frame is displayed

  Scenario: From the UI, no dashboard is embedded for a cluster without dashboard
    Given an account clusters "Client Compose" and a slug "client-compose" on docker compose
    And an account environment on "Demo Compose Cluster" for the environment "staging"
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It goes to the dashboard of "Demo Compose Cluster~staging"
    Then the dashboard frame is not displayed
