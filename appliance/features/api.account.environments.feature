@api
Feature: API endpoints to manage account's environments where deploy projects
  In order to manage account's environments
  As a user of an account
  I want to manage and create environments for my account

  On Space, projects are deployed on clusters's namespaces corresponding to desired an environment label for the
  deployment job. Clusters can be registered manually for each project or managed in the account.
  An account's environments is the result of the "namespace" installed for an environment "label" on a cluster instance
  available in the clusters catalog for the account. (The catalog aggregate clusters defined by adminsitrator and
  privates account's clusters). An account's environment can be reinstalled.
  Environments are immuable, they are not editable. Used account's environments in projects are not hardly linked :
  when an account's environment is recreated, projects must be refreshed.

  Background:
    Given a Space app instance
    And a memory document database

  Scenario: From the API, create a new environment, on a managed cluster, via a request with a form url encoded body
    Given a kubernetes client
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to get account's environments
    Then get a JSON response
    And the serialized account's environments of "My Company"
    When the API is called to update account's environments:
      | field                                             | value             |
      | space_account.environments.1.accountEnvironmentId | <auto:prod>       |
      | space_account.environments.1.clusterName          | Demo Kube Cluster |
      | space_account.environments.1.envName              | prod              |
      | space_account.environments.2.accountEnvironmentId |                   |
      | space_account.environments.2.clusterName          | Demo Kube Cluster |
      | space_account.environments.2.envName              | testing           |
    Then get a JSON response
    And the serialized account's environments of "My Company"
    And Space executes the pending tasks
    And a Kubernetes namespace for "my-company-testing" dedicated to "Demo Kube Cluster" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted on "Demo Kube Cluster"
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: From the API, create a new environment on a managed cluster, via a request with a json body
    Given a kubernetes client
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to get account's environments
    Then get a JSON response
    And the serialized account's environments of "My Company"
    When the API is called to update account's environments with a json body:
      | field                               | value             |
      | environments.1.accountEnvironmentId | <auto:prod>       |
      | environments.1.clusterName          | Demo Kube Cluster |
      | environments.1.envName              | prod              |
      | environments.2.accountEnvironmentId |                   |
      | environments.2.clusterName          | Demo Kube Cluster |
      | environments.2.envName              | testing           |
    Then get a JSON response
    And the serialized account's environments of "My Company"
    And Space executes the pending tasks
    And a Kubernetes namespace for "my-company-testing" dedicated to "Demo Kube Cluster" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted on "Demo Kube Cluster"
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: From the API, update a read only environment and get an error
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to get account's environments
    Then get a JSON response
    And the serialized account's environments of "My Company"
    When the API is called to update account's environments:
      | field                                             | value             |
      | space_account.environments.0.accountEnvironmentId | <auto:dev>        |
      | space_account.environments.0.clusterName          | Demo Kube Cluster |
      | space_account.environments.0.envName              | dev               |
      | space_account.environments.1.accountEnvironmentId | <auto:prod>       |
      | space_account.environments.1.clusterName          | Demo Kube Cluster |
      | space_account.environments.1.envName              | testing           |
    Then get a JSON response
    But the user must have a 400 error
    And no Kubernetes manifests have been deleted

  Scenario: From the API, update a read only environment, via a request with a json body and get an error
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to get account's environments
    Then get a JSON response
    And the serialized account's environments of "My Company"
    When the API is called to update account's environments with a json body:
      | field                               | value             |
      | environments.0.accountEnvironmentId | <auto:dev>        |
      | environments.0.clusterName          | Demo Kube Cluster |
      | environments.0.envName              | dev               |
      | environments.1.accountEnvironmentId | <auto:prod>       |
      | environments.1.clusterName          | Demo Kube Cluster |
      | environments.1.envName              | testing           |
    Then get a JSON response
    But the user must have a 400 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created

  Scenario: From the API, create an environment on a managed cluster and exceeding quota, via a request with a form url encoded body and get an error
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to get account's environments
    Then get a JSON response
    And the serialized account's environments of "My Company"
    When the API is called to update account's environments:
      | field                                             | value             |
      | space_account.environments.0.accountEnvironmentId | <auto:dev>        |
      | space_account.environments.0.clusterName          | Demo Kube Cluster |
      | space_account.environments.0.envName              | dev               |
      | space_account.environments.1.accountEnvironmentId | <auto:prod>       |
      | space_account.environments.1.clusterName          | Demo Kube Cluster |
      | space_account.environments.1.envName              | prod              |
      | space_account.environments.2.accountEnvironmentId |                   |
      | space_account.environments.2.clusterName          | Demo Kube Cluster |
      | space_account.environments.2.envName              | testing           |
    Then get a JSON response
    But the user must have a 400 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created

  Scenario: From the API, create an environment on a managed cluster and exceeding quota, via a request with a json body and get an error
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to get account's environments
    Then get a JSON response
    And the serialized account's environments of "My Company"
    When the API is called to update account's environments with a json body:
      | field                               | value             |
      | environments.0.accountEnvironmentId | <auto:dev>        |
      | environments.0.clusterName          | Demo Kube Cluster |
      | environments.0.envName              | dev               |
      | environments.1.accountEnvironmentId | <auto:prod>       |
      | environments.1.clusterName          | Demo Kube Cluster |
      | environments.1.envName              | prod              |
      | environments.2.accountEnvironmentId |                   |
      | environments.2.clusterName          | Demo Kube Cluster |
      | environments.2.envName              | testing           |
    Then get a JSON response
    But the user must have a 400 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created

  Scenario: From the API, create a new environment, on an account cluster, via a request with a form url encoded body
    Given a kubernetes client
    And an account for "My Company" with the account namespace "my-company"
    And an account clusters "Cluster Company" and a slug "my-company-cluster"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to get account's environments
    Then get a JSON response
    And the serialized account's environments of "My Company"
    When the API is called to update account's environments:
      | field                                             | value             |
      | space_account.environments.1.accountEnvironmentId | <auto:prod>       |
      | space_account.environments.1.clusterName          | Demo Kube Cluster |
      | space_account.environments.1.envName              | prod              |
      | space_account.environments.2.accountEnvironmentId |                   |
      | space_account.environments.2.clusterName          | Cluster Company   |
      | space_account.environments.2.envName              | testing           |
    Then get a JSON response
    And the serialized account's environments of "My Company"
    And Space executes the pending tasks
    And a Kubernetes namespace for "my-company-testing" dedicated to "Cluster Company" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted on "Demo Kube Cluster"
    And no Kubernetes manifests have been created on "Demo Kube Cluster"
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: From the API, create a new environment on an account cluster, via a request with a json body
    Given a kubernetes client
    And an account for "My Company" with the account namespace "my-company"
    And an account clusters "Cluster Company" and a slug "my-company-cluster"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to get account's environments
    Then get a JSON response
    And the serialized account's environments of "My Company"
    When the API is called to update account's environments with a json body:
      | field                               | value             |
      | environments.1.accountEnvironmentId | <auto:prod>       |
      | environments.1.clusterName          | Demo Kube Cluster |
      | environments.1.envName              | prod              |
      | environments.2.accountEnvironmentId |                   |
      | environments.2.clusterName          | Cluster Company   |
      | environments.2.envName              | testing           |
    Then get a JSON response
    And the serialized account's environments of "My Company"
    And Space executes the pending tasks
    And a Kubernetes namespace for "my-company-testing" dedicated to "Cluster Company" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted on "Demo Kube Cluster"
    And no Kubernetes manifests have been created on "Demo Kube Cluster"
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: From the API, create an environment on an account cluster and exceeding quota, via a request with a form url encoded body and get an error
    Given an account for "My Company" with the account namespace "my-company"
    And an account clusters "Cluster Company" and a slug "my-company-cluster"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to get account's environments
    Then get a JSON response
    And the serialized account's environments of "My Company"
    When the API is called to update account's environments:
      | field                                             | value             |
      | space_account.environments.0.accountEnvironmentId | <auto:dev>        |
      | space_account.environments.0.clusterName          | Demo Kube Cluster |
      | space_account.environments.0.envName              | dev               |
      | space_account.environments.1.accountEnvironmentId | <auto:prod>       |
      | space_account.environments.1.clusterName          | Demo Kube Cluster |
      | space_account.environments.1.envName              | prod              |
      | space_account.environments.2.accountEnvironmentId |                   |
      | space_account.environments.2.clusterName          | Cluster Company   |
      | space_account.environments.2.envName              | testing           |
    Then get a JSON response
    But the user must have a 400 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created

  Scenario: From the API, create an environment on an account cluster and exceeding quota, via a request with a json body and get an error
    Given an account for "My Company" with the account namespace "my-company"
    And an account clusters "Cluster Company" and a slug "my-company-cluster"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to get account's environments
    Then get a JSON response
    And the serialized account's environments of "My Company"
    When the API is called to update account's environments with a json body:
      | field                                             | value             |
      | space_account.environments.0.accountEnvironmentId | <auto:dev>        |
      | space_account.environments.0.clusterName          | Demo Kube Cluster |
      | space_account.environments.0.envName              | dev               |
      | space_account.environments.1.accountEnvironmentId | <auto:prod>       |
      | space_account.environments.1.clusterName          | Demo Kube Cluster |
      | space_account.environments.1.envName              | prod              |
      | space_account.environments.2.accountEnvironmentId |                   |
      | space_account.environments.2.clusterName          | Cluster Company   |
      | space_account.environments.2.envName              | testing           |
    Then get a JSON response
    But the user must have a 400 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created
