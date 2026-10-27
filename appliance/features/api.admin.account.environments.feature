@api @admin
Feature: API admin endpoints to administrate environments of accounts, where projects are deployed
  In order to keep the environments of the accounts in the hands of their users
  As an administrator of Space
  I can refresh the quotas of an account, but I can not create, delete or reinstall its environments

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

  Scenario: From the API, as Admin, create a new environment, on a managed cluster, via a request with a form url encoded body is refused
    Given a kubernetes client
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to edit the last account environments:
      | field                                                   | value             |
      | admin_space_account.environments.1.accountEnvironmentId | <auto:prod>       |
      | admin_space_account.environments.1.clusterName          | Demo Kube Cluster |
      | admin_space_account.environments.1.envName              | prod              |
      | admin_space_account.environments.2.accountEnvironmentId |                   |
      | admin_space_account.environments.2.clusterName          | Demo Kube Cluster |
      | admin_space_account.environments.2.envName              | testing           |
    Then get a JSON response
    But an 403 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created

  Scenario: From the API, as Admin, create a new environment on a managed cluster, via a request with a json body is refused
    Given a kubernetes client
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to edit the last account environments with a json body:
      | field                               | value             |
      | environments.1.accountEnvironmentId | <auto:prod>       |
      | environments.1.clusterName          | Demo Kube Cluster |
      | environments.1.envName              | prod              |
      | environments.2.accountEnvironmentId |                   |
      | environments.2.clusterName          | Demo Kube Cluster |
      | environments.2.envName              | Testing           |
    Then get a JSON response
    But an 403 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created

  Scenario: From the API, as Admin, create an environment on a managed cluster and exceeding quota, via a request with a form url encoded body is refused
    Given an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to edit the last account environments:
      | field                                                   | value             |
      | admin_space_account.environments.0.accountEnvironmentId | <auto:dev>        |
      | admin_space_account.environments.0.clusterName          | Demo Kube Cluster |
      | admin_space_account.environments.0.envName              | dev               |
      | admin_space_account.environments.1.accountEnvironmentId | <auto:prod>       |
      | admin_space_account.environments.1.clusterName          | Demo Kube Cluster |
      | admin_space_account.environments.1.envName              | prod              |
      | admin_space_account.environments.2.accountEnvironmentId |                   |
      | admin_space_account.environments.2.clusterName          | Demo Kube Cluster |
      | admin_space_account.environments.2.envName              | Testing           |
    Then get a JSON response
    But an 403 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created

  Scenario: From the API, as Admin, refresh account's quota on its environments
    Given a kubernetes client
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to refresh quota of account's environment
    Then get a JSON response
    And the serialized success result
    And Space executes the pending tasks
    And a Kubernetes manifests dedicated to quota for the last account has been applied
    And no Kubernetes manifests have been deleted
    And no object has been deleted

  Scenario: From the API, as Admin, refresh account's quota on its environments spread over several clusters
    Given a kubernetes client
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an account clusters "Cluster Company" and a slug "cluster-company"
    And an account environment on "Cluster Company" for the environment "testing"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to refresh quota of account's environment
    Then get a JSON response
    And the serialized success result
    And Space executes the pending tasks
    And a Kubernetes manifests dedicated to quota for the last account has been applied
    And no Kubernetes manifests have been deleted
    And no object has been deleted

  Scenario: From the API, as Admin, refresh account's quota on its environments, some on a Docker Compose cluster
    Given a kubernetes client
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an account clusters "Docker Host" and a slug "docker-host" on docker compose
    And an account environment on "Docker Host" for the environment "staging"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to refresh quota of account's environment
    Then get a JSON response
    And the serialized success result
    And Space executes the pending tasks
    And a Kubernetes manifests dedicated to quota for the last account has been applied
    And no docker compose configuration must be created
    And the account history must record the quota refresh skipped for "staging" on "Docker Host"
    And no Kubernetes manifests have been deleted
    And no object has been deleted

  Scenario: From the API, as Admin, refresh account's quota twice, both tasks consumed by the same worker
    Given a kubernetes client
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an account clusters "Cluster Company" and a slug "cluster-company"
    And an account environment on "Cluster Company" for the environment "testing"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to refresh quota of account's environment
    Then get a JSON response
    And the serialized success result
    When the API is called to refresh quota of account's environment
    Then get a JSON response
    And the serialized success result
    And Space executes the pending tasks
    And a Kubernetes manifests dedicated to quota for the last account has been applied 2 times
    And no Kubernetes manifests have been deleted
    And no object has been deleted

  Scenario: From the API, as Admin, reinstall an account's environment is refused
    Given a kubernetes client
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to reinstall the account's environment "prod" on "Demo Kube Cluster"
    Then get a JSON response
    But an 403 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created
