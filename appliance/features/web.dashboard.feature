@web
Feature: Web dashboard embedding the web dashboard of the selected cluster
  In order to follow my workloads from Space
  As a user of an account
  I want to see the web dashboard of the cluster hosting my environment, without signing in on it

  The dashboard page embeds, in an iframe, the web dashboard of the cluster hosting the selected environment. Space
  relays it and injects the environment's credentials, so the user never signs in on the dashboard. A cluster without
  a dashboard address or without a dashboard type shows no iframe. The dashboard type selects the profile of the
  relay: Headlamp is served under the base path `/__headlamp`, rewritten by the relay into the path of the frame.
  Websockets and streamed requests, used by dashboards for live updates, logs and terminals, are not relayed.

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
    And the dashboard frame opens "/dashboard/frame/demo-kube-cluster/dev/c/main/workloads?namespace=space-client-my-company-dev"

  Scenario: From the UI, no dashboard is embedded for a cluster without dashboard
    Given an account clusters "Client Compose" and a slug "client-compose" on docker compose
    And an account environment on "Demo Compose Cluster" for the environment "staging"
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It goes to the dashboard of "Demo Compose Cluster~staging"
    Then the dashboard frame is not displayed

  Scenario: From the UI, the dashboard frame of a cluster without dashboard is not found
    Given an account clusters "Client Compose" and a slug "client-compose" on docker compose
    And an account environment on "Demo Compose Cluster" for the environment "staging"
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It opens the dashboard frame of "demo-compose-cluster" for "staging"
    Then the user must have a 404 error

  Scenario: From the UI, the dashboard page supports an environment on a cluster registered by the account
    Given an account clusters "Client Kube" and a slug "client-kube"
    And an account environment on "Client Kube" for the environment "staging"
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It goes to the dashboard of "Client Kube~staging"
    Then the dashboard frame is not displayed

  Scenario: From the UI, the dashboard of a cluster registered by the account is not relayed
    Given an account clusters "Client Kube" and a slug "client-kube"
    And an account environment on "Client Kube" for the environment "staging"
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It opens the dashboard frame of "client-kube" for "staging"
    Then the user must have a 404 error

  Scenario: From the UI, the Headlamp dashboard of the environment is relayed with its credentials
    Given a kubernetes client
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It opens the dashboard frame of "demo-kube-cluster" for "dev"
    Then the dashboard received a "GET" request to "https://dashboard.kubernetes.localhost/__headlamp/" with the token "aFakeToken"
    And the dashboard page is served under "/dashboard/frame/demo-kube-cluster/dev"
    And the dashboard page restricts Headlamp to the namespace "space-client-my-company-dev"

  Scenario: From the UI, the requests of the dashboard are relayed with their query string and their body
    Given a kubernetes client
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It sends a "POST" request to "clusters/main/apis/authorization.k8s.io/v1/selfsubjectrulesreviews?dryRun=All" on the dashboard frame of "demo-kube-cluster" for "dev"
      """
      {"spec":{"namespace":"space-client-my-company-dev"}}
      """
    Then the dashboard received a "POST" request to "https://dashboard.kubernetes.localhost/__headlamp/clusters/main/apis/authorization.k8s.io/v1/selfsubjectrulesreviews?dryRun=All" with the token "aFakeToken"
    And the dashboard received the body:
      """
      {"spec":{"namespace":"space-client-my-company-dev"}}
      """

  Scenario: From the UI, a request sent to the dashboard by another site is refused
    Given a kubernetes client
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When another site sends a "DELETE" request to "clusters/main/api/v1/namespaces/space-client-my-company-dev/pods/foo" on the dashboard frame of "demo-kube-cluster" for "dev"
    Then the user must have a 403 error
    And the dashboard was not reached

  Scenario: From the UI, the live updates of the dashboard are refused at once
    Given a kubernetes client
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It sends a "GET" request to "clusters/main/api/v1/namespaces/space-client-my-company-dev/pods?watch=1" on the dashboard frame of "demo-kube-cluster" for "dev"
    Then the user must have a 501 error
    And the dashboard was not reached

  Scenario: From the UI, a user can not open the dashboard of an environment it does not own
    Given a kubernetes client
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It opens the dashboard frame of "demo-kube-cluster" for "staging"
    Then the user must have a 403 error
    And the dashboard was not reached

  Scenario: From the UI, an administrator opens the Headlamp dashboard of a cluster on all namespaces
    Given a kubernetes client
    And an admin, called "Admin" "Space" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the user is signed in with "admin@teknoo.space" and the password "Test2@Test"
    When It opens the dashboard frame of "demo-kube-cluster" for "_all"
    Then the dashboard received a "GET" request to "https://dashboard.kubernetes.localhost/__headlamp/" with the token "fooBar"
    And the dashboard page is served under "/dashboard/frame/demo-kube-cluster/_all"
    And the dashboard page lets Headlamp show all namespaces

  Scenario: From the UI, the legacy Kubernetes Dashboard is embedded and relayed with its own profile
    Given a kubernetes client
    And the cluster "Demo Kube Cluster" uses the legacy Kubernetes Dashboard
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    When It goes to the dashboard of "Demo Kube Cluster~dev"
    Then the dashboard frame opens "/dashboard/frame/demo-kube-cluster/dev/#/workloads?namespace=space-client-my-company-dev"
    When It opens the dashboard frame of "demo-kube-cluster" for "dev"
    Then the dashboard received a "GET" request to "https://dashboard.kubernetes.localhost/__headlamp/" with the token "aFakeToken"
    And the dashboard page has the base "https://localhost/dashboard/frame/demo-kube-cluster/dev/"
