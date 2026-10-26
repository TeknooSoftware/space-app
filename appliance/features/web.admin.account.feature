@web @admin
Feature: Web admin interface to manage accounts
  In order to manage accounts
  As an administrator of Space
  I want to refresh the quotas of an account and reinstall its registry from the administration

  The provisioning actions of the administration change the clusters of an account: they are sent with a POST and the
  CSRF token of the session, a link (GET) or a request without the token can not start them.

  Background:
    Given a Space app instance
    And a memory document database
    And a kubernetes client
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    And it is redirected to the dashboard

  Scenario: From the UI, as Admin, the quota of an account can not be refreshed with a link
    When it calls the admin action "refresh quota" of the last account with the method "GET"
    Then the user must have a 405 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created
    And no Kubernetes manifests have been deleted

  Scenario: From the UI, as Admin, the quota of an account can not be refreshed without the CSRF token
    When it calls the admin action "refresh quota" of the last account with the method "POST"
    Then the user must have a 403 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created
    And no Kubernetes manifests have been deleted

  Scenario: From the UI, as Admin, refresh the quota of an account
    When it calls the admin action "refresh quota" of the last account with the method "POST" and the CSRF token
    Then it is redirected to the admin page of the last account
    And Space executes the pending tasks
    And a Kubernetes manifests dedicated to quota for the last account has been applied
    And no Kubernetes manifests have been deleted

  Scenario: From the UI, as Admin, the registry of an account can not be reinstalled with a link
    When it calls the admin action "reinstall registry" of the last account with the method "GET"
    Then the user must have a 405 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created
    And no Kubernetes manifests have been deleted

  Scenario: From the UI, as Admin, the registry of an account can not be reinstalled without the CSRF token
    When it calls the admin action "reinstall registry" of the last account with the method "POST"
    Then the user must have a 403 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created
    And no Kubernetes manifests have been deleted

  Scenario: From the UI, as Admin, reinstall the registry of an account
    When it calls the admin action "reinstall registry" of the last account with the method "POST" and the CSRF token
    Then it is redirected to the admin page of the last account
    And Space executes the pending tasks
    And a Kubernetes namespace dedicated to registry for "my-company" is applied and populated on "Demo Kube Cluster"
    And no Kubernetes manifests have been deleted
