@web
Feature: Dashboard of a user without account
  In order to know why I can not use Space
  As a user of Space without account
  I want an empty dashboard, with a message that explains my situation

  A user without account is a user of no account and is not an administrator of Space. Its dashboard shows no
  cluster of the platform, no health and no version.

  Background:
    Given a Space app instance
    And a memory document database
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user

  Scenario: From the UI, the dashboard of a user without account shows no cluster of the platform
    Given the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When it opens the page of the route "space_dashboard"
    Then get a valid web page
    And the page does not show "Demo Kube Cluster"
    And the page shows the message "teknoo.space.text.dashboard.without_account"
