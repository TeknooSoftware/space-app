Feature: API endpoints to manage account's environments where deploy projects
  In order to manage account's environments
  As an user of an account
  I want to manage and create environments for my account

  On Space, projects are deployed on clusters's namespaces corresponding to desired an environment label for the
  deployment job. Clusters can be registered manually for each project or managed in the account.
  An account's environments is the result of the "namespace" installed for an environment "label" on a cluster instance
  available in the clusters catalog for the account. (The catalog aggregate clusters defined by adminsitrator and
  privates account's clusters). An account's environment can be reinstalled.
  Environments are immuable, they are not editable. Used account's environments in projects are not hardly linked :
  when an account's environment is recreated, projects must be refreshed.

  Scenario: From the API, create a new environment, on a managed cluster, via a request with a form url encoded body
    Given A Space app instance
    And A memory document database
    And a kubernetes client
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's environments
    Then get a JSON reponse
    And the serialized account's environments of "My Company"
    When the API is called to update account's environments:
      | field                                             | value             |
      | space_account.environments.1.accountEnvironmentId | <auto:prod>       |
      | space_account.environments.1.clusterName          | Demo Kube Cluster |
      | space_account.environments.1.envName              | prod              |
      | space_account.environments.2.accountEnvironmentId |                   |
      | space_account.environments.2.clusterName          | Demo Kube Cluster |
      | space_account.environments.2.envName              | testing           |
    Then get a JSON reponse
    And the serialized account's environments of "My Company"
    And a Kubernetes namespace for "my-company-testing" dedicated to "Demo Kube Cluster" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted on "Demo Kube Cluster"
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: From the api, create a new environment on a managed cluster, via a request with a json body
    Given A Space app instance
    And A memory document database
    And a kubernetes client
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's environments
    Then get a JSON reponse
    And the serialized account's environments of "My Company"
    When the API is called to update account's environments with a json body:
      | field                               | value             |
      | environments.1.accountEnvironmentId | <auto:prod>       |
      | environments.1.clusterName          | Demo Kube Cluster |
      | environments.1.envName              | prod              |
      | environments.2.accountEnvironmentId |                   |
      | environments.2.clusterName          | Demo Kube Cluster |
      | environments.2.envName              | testing           |
    Then get a JSON reponse
    And the serialized account's environments of "My Company"
    And a Kubernetes namespace for "my-company-testing" dedicated to "Demo Kube Cluster" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted on "Demo Kube Cluster"
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: From the API, update an read only environment and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's environments
    Then get a JSON reponse
    And the serialized account's environments of "My Company"
    When the API is called to update account's environments:
      | field                                             | value             |
      | space_account.environments.0.accountEnvironmentId | <auto:dev>        |
      | space_account.environments.0.clusterName          | Demo Kube Cluster |
      | space_account.environments.0.envName              | dev               |
      | space_account.environments.1.accountEnvironmentId | <auto:prod>       |
      | space_account.environments.1.clusterName          | Demo Kube Cluster |
      | space_account.environments.1.envName              | testing           |
    Then get a JSON reponse
    But the user must have a 400 error
    And no Kubernetes manifests must not be deleted

  Scenario: From the API, update an read only environment, via a request with a json body and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's environments
    Then get a JSON reponse
    And the serialized account's environments of "My Company"
    When the API is called to update account's environments with a json body:
      | field                               | value             |
      | environments.0.accountEnvironmentId | <auto:dev>        |
      | environments.0.clusterName          | Demo Kube Cluster |
      | environments.0.envName              | dev               |
      | environments.1.accountEnvironmentId | <auto:prod>       |
      | environments.1.clusterName          | Demo Kube Cluster |
      | environments.1.envName              | testing           |
    Then get a JSON reponse
    But the user must have a 400 error
    And no Kubernetes manifests must not be created

  Scenario: From the API, create an environmnt on a managed cluster and exceeding quota, via a request with a form
  url encoded body and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's environments
    Then get a JSON reponse
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
    Then get a JSON reponse
    But the user must have a 400 error
    And no Kubernetes manifests must not be created

  Scenario: From the API, create an environment on a managed cluster and exceeding quota, via a request with a json
  body and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's environments
    Then get a JSON reponse
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
    Then get a JSON reponse
    But the user must have a 400 error
    And no Kubernetes manifests must not be created

  Scenario: From the API, create a new environment, on an account cluster, via a request with a form url encoded body
    Given A Space app instance
    And A memory document database
    And a kubernetes client
    And an account for "My Company" with the account namespace "my-company"
    And an account clusters "Cluster Company" and a slug "my-company-cluster"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's environments
    Then get a JSON reponse
    And the serialized account's environments of "My Company"
    When the API is called to update account's environments:
      | field                                             | value             |
      | space_account.environments.1.accountEnvironmentId | <auto:prod>       |
      | space_account.environments.1.clusterName          | Demo Kube Cluster |
      | space_account.environments.1.envName              | prod              |
      | space_account.environments.2.accountEnvironmentId |                   |
      | space_account.environments.2.clusterName          | Cluster Company   |
      | space_account.environments.2.envName              | testing           |
    Then get a JSON reponse
    And the serialized account's environments of "My Company"
    And a Kubernetes namespace for "my-company-testing" dedicated to "Cluster Company" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted on "Demo Kube Cluster"
    And no Kubernetes manifests must not be created on "Demo Kube Cluster"
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: From the api, create a new environment on an account cluster, via a request with a json body
    Given A Space app instance
    And A memory document database
    And a kubernetes client
    And an account for "My Company" with the account namespace "my-company"
    And an account clusters "Cluster Company" and a slug "my-company-cluster"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's environments
    Then get a JSON reponse
    And the serialized account's environments of "My Company"
    When the API is called to update account's environments with a json body:
      | field                               | value             |
      | environments.1.accountEnvironmentId | <auto:prod>       |
      | environments.1.clusterName          | Demo Kube Cluster |
      | environments.1.envName              | prod              |
      | environments.2.accountEnvironmentId |                   |
      | environments.2.clusterName          | Cluster Company   |
      | environments.2.envName              | testing           |
    Then get a JSON reponse
    And the serialized account's environments of "My Company"
    And a Kubernetes namespace for "my-company-testing" dedicated to "Cluster Company" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted on "Demo Kube Cluster"
    And no Kubernetes manifests must not be created on "Demo Kube Cluster"
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: From the API, create an environmnt on an account cluster and exceeding quota, via a request with a form
  url encoded body and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an account clusters "Cluster Company" and a slug "my-company-cluster"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's environments
    Then get a JSON reponse
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
    Then get a JSON reponse
    But the user must have a 400 error
    And no Kubernetes manifests must not be created

  Scenario: From the API, create an environment on an account cluster and exceeding quota, via a request with a json
  body and get an error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an account clusters "Cluster Company" and a slug "my-company-cluster"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's environments
    Then get a JSON reponse
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
    Then get a JSON reponse
    But the user must have a 400 error
    And no Kubernetes manifests must not be created