@web
Feature: Web dashboard of the cluster hosting an environment
  In order to follow my workloads from Space
  As a user of an account
  I want to see the web dashboard of the cluster hosting my environment, without signing in on it

  A cluster has a web dashboard when it has a dashboard address and a dashboard type. The dashboard type tells how
  the dashboard is embedded, like its entry page.
  Space shows the dashboard in a frame and relays its requests: it adds the credential of the environment to each
  request, so the user never signs in on the dashboard. The cookies of Space are never sent to the dashboard.
  A user reaches only the dashboards of the environments of its account, opened on the namespace of the environment.
  An administrator reaches the dashboard of a cluster on all namespaces, with the credential of the cluster.
  The dashboard of a cluster registered by an account is shown only when the operator allows it, with
  `SPACE_DASHBOARD_EXTERNAL_ENABLED`. It is not allowed in these scenarios.

  Background:
    Given a Space app instance
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user

  Scenario: From the UI, the dashboard of the cluster hosting the environment is shown on the environment's namespace
    Given the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It goes to the dashboard of "Demo Kube Cluster~dev"
    Then the dashboard frame opens "/dashboard/frame/demo-kube-cluster/dev/c/main/workloads?namespace=space-client-my-company-dev"

  Scenario: From the UI, the dashboard type of a cluster selects the entry page of its dashboard
    Given the dashboard type of the cluster "Demo Kube Cluster" is "kubernetes-dashboard"
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It goes to the dashboard of "Demo Kube Cluster~dev"
    Then the dashboard frame opens "/dashboard/frame/demo-kube-cluster/dev/#/workloads?namespace=space-client-my-company-dev"

  Scenario: From the UI, no dashboard is shown for a cluster without dashboard
    Given an account clusters "Client Compose" and a slug "client-compose" on docker compose
    And an account environment on "Demo Compose Cluster" for the environment "staging"
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It goes to the dashboard of "Demo Compose Cluster~staging"
    Then the dashboard frame is not displayed

  Scenario: From the UI, the dashboard of a cluster without dashboard is not found
    Given an account clusters "Client Compose" and a slug "client-compose" on docker compose
    And an account environment on "Demo Compose Cluster" for the environment "staging"
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It opens the dashboard frame of "demo-compose-cluster" for "staging"
    Then the user must have a 404 error

  Scenario: From the UI, the dashboard of a cluster registered by the account is not shown when the operator does not allow it
    Given an account clusters "Client Kube" and a slug "client-kube" with a "headlamp" dashboard
    And an account environment on "Client Kube" for the environment "staging"
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It goes to the dashboard of "Client Kube~staging"
    Then the dashboard frame is not displayed

  Scenario: From the UI, the dashboard of a cluster registered by the account is not relayed when the operator does not allow it
    Given an account clusters "Client Kube" and a slug "client-kube" with a "headlamp" dashboard
    And an account environment on "Client Kube" for the environment "staging"
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It opens the dashboard frame of "client-kube" for "staging"
    Then the user must have a 404 error

  Scenario: From the UI, the dashboard is relayed with the credential of the environment, without the cookies of Space
    Given a kubernetes client
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It opens the dashboard frame of "demo-kube-cluster" for "dev"
    Then the dashboard received a "GET" request to "https://dashboard.kubernetes.localhost/__headlamp/" with the token "aFakeToken"

  Scenario: From the UI, the requests of the dashboard are relayed with their query string and their body
    Given a kubernetes client
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It sends a "POST" request to "api/items?page=2" on the dashboard frame of "demo-kube-cluster" for "dev"
      """
      {"name":"foo"}
      """
    Then the dashboard received a "POST" request to "https://dashboard.kubernetes.localhost/__headlamp/api/items?page=2" with the token "aFakeToken"
    And the dashboard received the body:
      """
      {"name":"foo"}
      """

  Scenario: From the UI, a request sent to the dashboard by another site is refused, it would use the credential of the environment
    Given a kubernetes client
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When another site sends a "DELETE" request to "api/items/42" on the dashboard frame of "demo-kube-cluster" for "dev"
    Then the user must have a 403 error
    And the dashboard was not reached

  Scenario: From the UI, a user can not open the dashboard of an environment its account does not own
    Given a kubernetes client
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It opens the dashboard frame of "demo-kube-cluster" for "staging"
    Then the user must have a 403 error
    And the dashboard was not reached

  Scenario: From the UI, an administrator opens the dashboard of a cluster on all namespaces, with the credential of the cluster
    Given a kubernetes client
    And an admin, called "Admin" "Space" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the user is signed in with "admin@teknoo.space" and the password "Test2@Test"
    When It opens the dashboard frame of "demo-kube-cluster" for "_all"
    Then the dashboard received a "GET" request to "https://dashboard.kubernetes.localhost/__headlamp/" with the token "fooBar"
