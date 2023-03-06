Feature: On a space instance, users are grouped in shared accounts : there are at least one user per account, but
  account can have several users. The account and its projects is shared by all users of the account.
  An account owns also a specific Kubernetes namespace, billing informations and a name of the company or the service
  behind the account.

  Scenario: Update my account settings
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to account settings
    Then the user obtains the form:
      | field                                   | value          |
      | space_account.account.name              | My Company     |
      | space_account.accountData.billingName   | My Company SAS |
      | space_account.accountData.streetAddress | 123 street     |
      | space_account.accountData.zipCode       | 14000          |
      | space_account.accountData.cityName      | Caen           |
      | space_account.accountData.countryName   | France         |
      | space_account.accountData.vatNumber     | FR0102030405   |
    When it submits the form:
      | field                                   | value          |
      | space_account._token                    | <auto>         |
      | space_account.account.name              | Great Company  |
      | space_account.accountData.billingName   | Gr Company SAS |
      | space_account.accountData.streetAddress | 123 street     |
      | space_account.accountData.zipCode       | 14000          |
      | space_account.accountData.cityName      | Caen           |
      | space_account.accountData.countryName   | France         |
      | space_account.accountData.vatNumber     | FR0102030405   |
    Then the user obtains the form:
      | field                                   | value          |
      | space_account.account.name              | Great Company  |
      | space_account.accountData.billingName   | Gr Company SAS |
      | space_account.accountData.streetAddress | 123 street     |
      | space_account.accountData.zipCode       | 14000          |
      | space_account.accountData.cityName      | Caen           |
      | space_account.accountData.countryName   | France         |
      | space_account.accountData.vatNumber     | FR0102030405   |
    And the account name is now "Great Company"
