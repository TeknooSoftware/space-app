Feature: On space, users are logged with an email and a password.
  Users can enable 2FA authentication, with a TOTP application.

  Scenario: Login without 2FA
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it is redirected to the dashboard
    And a session is opened
    And It has a welcome message with "Jean Dupont" in the dashboard header

  Scenario: Login with 2FA
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    Then it is redirected to the dashboard
    And a session is opened
    And It has a welcome message with "Jean Dupont" in the dashboard header

  Scenario: Login fail with wrong credential
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "foo@bar"
    Then it is redirected to the login page with an error

  Scenario: Login fail with wrong 2FA
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a wrong TOTP code
    Then it must redirected to the TOTP code page
    And it must have a TOTP error

  Scenario: Do nothing when user test a non existant email with the recovery method
    Given A Space app instance
    And A memory document database
    And the platform is booted
    When an user go to recovery request page
    Then the user obtains the form:
      | field            | value |
      | email_form.email |       |
    When it submits the form:
      | field             | value               |
      | email_form.email  | dupont@teknoo.space |
      | email_form._token | <auto>              |
    Then The client must go to recovery request sent page
    And a session must be not opened
    And no notification must be sent

  Scenario: Send notification and login when subscribed user input its email with the recovery method and follow the link
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    When an user go to recovery request page
    Then the user obtains the form:
      | field            | value |
      | email_form.email |       |
    When it submits the form:
      | field             | value               |
      | email_form.email  | dupont@teknoo.space |
      | email_form._token | <auto>              |
    Then The client must go to recovery request sent page
    And a session must be not opened
    And a notification must be sent
    When the user click on the link in the notification
    Then it is redirected to the recovery password page
    And a recovery session is opened

  Scenario: Send notification and login when subscribed user test its email with TOTP with the recovery method and follow the link
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When an user go to recovery request page
    Then the user obtains the form:
      | field            | value |
      | email_form.email |       |
    When it submits the form:
      | field             | value               |
      | email_form.email  | dupont@teknoo.space |
      | email_form._token | <auto>              |
    Then The client must go to recovery request sent page
    And a session must be not opened
    And a notification must be sent
    When the user click on the link in the notification
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    Then it is redirected to the recovery password page
    And a recovery session is opened
