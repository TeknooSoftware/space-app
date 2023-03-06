Feature: On a space instance, subscription can be restricted to some user with a code, given by the commercial teams,
  associated with the company name. Without its code, if the restriction is enabled, the subscription will fail.
  On success, Space will also create namespace on the Kubernetes cluster, private OCI registry and service account on
  Kubernetes.

  Scenario: Subscription with a non valid code on a subscription limited instance
    Given A Space app instance
    And a kubernetes client
    And a subscription restriction
    And A memory document database
    And the platform is booted
    When an user go to subscription page
    Then the user obtains the form:
      | field                                                       | value          |
      | space_subscription.user.user.firstName                      |                |
      | space_subscription.user.user.lastName                       |                |
      | space_subscription.account.account.name                     |                |
      | space_subscription.user.user.email                          |                |
      | space_subscription.user.user.storedPassword.password.first  |                |
      | space_subscription.user.user.storedPassword.password.second |                |
      | space_subscription.account.accountData.billingName          |                |
      | space_subscription.account.accountData.streetAddress        |                |
      | space_subscription.account.accountData.zipCode              |                |
      | space_subscription.account.accountData.cityName             |                |
      | space_subscription.account.accountData.countryName          |                |
      | space_subscription.account.accountData.vatNumber            |                |
      | space_subscription.code                                     |                |
    When it submits the form:
      | field                                                       | value          |
      | space_subscription._token                                   | <auto>         |
      | space_subscription.user.user.firstName                      | Jean           |
      | space_subscription.user.user.lastName                       | Dupont         |
      | space_subscription.account.account.name                     | My Company     |
      | space_subscription.user.user.email                          | jean@dupont.me |
      | space_subscription.user.user.storedPassword.password.first  | Test2@Test     |
      | space_subscription.user.user.storedPassword.password.second | Test2@Test     |
      | space_subscription.account.accountData.billingName          | MC SASU        |
      | space_subscription.account.accountData.streetAddress        | 123 Street     |
      | space_subscription.account.accountData.zipCode              | 14000          |
      | space_subscription.account.accountData.cityName             | Caen           |
      | space_subscription.account.accountData.countryName          | France         |
      | space_subscription.account.accountData.vatNumber            | FR0102030405   |
      | space_subscription.code                                     | AAAAAAA        |
    Then the user obtains an error
    And an invalid code error
    And the user obtains the form:
      | field                                                       | value          |
      | space_subscription.user.user.firstName                      | Jean           |
      | space_subscription.user.user.lastName                       | Dupont         |
      | space_subscription.account.account.name                     | My Company     |
      | space_subscription.user.user.email                          | jean@dupont.me |
      | space_subscription.user.user.storedPassword.password.first  |                |
      | space_subscription.user.user.storedPassword.password.second |                |
      | space_subscription.account.accountData.billingName          | MC SASU        |
      | space_subscription.account.accountData.streetAddress        | 123 Street     |
      | space_subscription.account.accountData.zipCode              | 14000          |
      | space_subscription.account.accountData.cityName             | Caen           |
      | space_subscription.account.accountData.countryName          | France         |
      | space_subscription.account.accountData.vatNumber            | FR0102030405   |
      | space_subscription.code                                     | AAAAAAA        |

  Scenario: Subscription with a valid code on a subscription limited instance
    Given A Space app instance
    And a kubernetes client
    And a subscription restriction
    And A memory document database
    And the platform is booted
    When an user go to subscription page
    Then the user obtains the form:
      | field                                                       | value          |
      | space_subscription.user.user.firstName                      |                |
      | space_subscription.user.user.lastName                       |                |
      | space_subscription.account.account.name                     |                |
      | space_subscription.user.user.email                          |                |
      | space_subscription.user.user.storedPassword.password.first  |                |
      | space_subscription.user.user.storedPassword.password.second |                |
      | space_subscription.account.accountData.billingName          |                |
      | space_subscription.account.accountData.streetAddress        |                |
      | space_subscription.account.accountData.zipCode              |                |
      | space_subscription.account.accountData.cityName             |                |
      | space_subscription.account.accountData.countryName          |                |
      | space_subscription.account.accountData.vatNumber            |                |
      | space_subscription.code                                     |                |
    When it submits the form:
      | field                                                       | value          |
      | space_subscription._token                                   | <auto>         |
      | space_subscription.user.user.firstName                      | Jean           |
      | space_subscription.user.user.lastName                       | Dupont         |
      | space_subscription.account.account.name                     | My Company     |
      | space_subscription.user.user.email                          | jean@dupont.me |
      | space_subscription.user.user.storedPassword.password.first  | Test2@Test     |
      | space_subscription.user.user.storedPassword.password.second | Test2@Test     |
      | space_subscription.account.accountData.billingName          | MC SASU        |
      | space_subscription.account.accountData.streetAddress        | 123 Street     |
      | space_subscription.account.accountData.zipCode              | 14000          |
      | space_subscription.account.accountData.cityName             | Caen           |
      | space_subscription.account.accountData.countryName          | France         |
      | space_subscription.account.accountData.vatNumber            | FR0102030405   |
      | space_subscription.code                                     | NWQ4MTC1       |
    Then An account "My Company" is created
    And an user "jean@dupont.me" is created
    And a Kubernetes namespace "my-company" is created and populated
    And a session is opened
    And the user is redirected to the dashboard page

  Scenario: Subscription on a unlimited instance
    Given A Space app instance
    And a kubernetes client
    And without a subscription restriction
    And A memory document database
    And the platform is booted
    When an user go to subscription page
    Then the user obtains the form:
      | field                                                       | value          |
      | space_subscription.user.user.firstName                      |                |
      | space_subscription.user.user.lastName                       |                |
      | space_subscription.account.account.name                     |                |
      | space_subscription.user.user.email                          |                |
      | space_subscription.user.user.storedPassword.password.first  |                |
      | space_subscription.user.user.storedPassword.password.second |                |
      | space_subscription.account.accountData.billingName          |                |
      | space_subscription.account.accountData.streetAddress        |                |
      | space_subscription.account.accountData.zipCode              |                |
      | space_subscription.account.accountData.cityName             |                |
      | space_subscription.account.accountData.countryName          |                |
      | space_subscription.account.accountData.vatNumber            |                |
    When it submits the form:
      | field                                                       | value          |
      | space_subscription._token                                   | <auto>         |
      | space_subscription.user.user.firstName                      | Jean           |
      | space_subscription.user.user.lastName                       | Dupont         |
      | space_subscription.account.account.name                     | My Company     |
      | space_subscription.user.user.email                          | jean@dupont.me |
      | space_subscription.user.user.storedPassword.password.first  | Test2@Test     |
      | space_subscription.user.user.storedPassword.password.second | Test2@Test     |
      | space_subscription.account.accountData.billingName          | MC SASU        |
      | space_subscription.account.accountData.streetAddress        | 123 Street     |
      | space_subscription.account.accountData.zipCode              | 14000          |
      | space_subscription.account.accountData.cityName             | Caen           |
      | space_subscription.account.accountData.countryName          | France         |
      | space_subscription.account.accountData.vatNumber            | FR0102030405   |
    Then An account "My Company" is created
    And an user "jean@dupont.me" is created
    And a Kubernetes namespace "my-company" is created and populated
    And a session is opened
    And the user is redirected to the dashboard page