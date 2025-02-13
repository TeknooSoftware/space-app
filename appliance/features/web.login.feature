Feature: Web interface to allow users to login
  In order to login user
  As an user of an account
  I want to log in into my Space instance

  On space, users are logged with an email and a password. Users can enable 2FA authentication, with a TOTP application.
  A recovery method via a notification sent by mail allow users to log in when they lost theirs passwords. But if the
  2FA authentication is enabled, a TOTP token still required to finish login.

  Scenario: From the UI, login without 2FA enabled for the user
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it is redirected to the dashboard
    And a session is opened
    And It has a welcome message with "Jean Dupont" in the dashboard header

  Scenario: From the UI, login with 2FA enabled for the user
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

  Scenario: From the UI, login fail with wrong credential
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "foo@bar"
    Then it is redirected to the login page with an error

  Scenario: From the UI, login fail with wrong TOTP token with 2FA enabled for the user
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

  Scenario: From the UI, test login via the recovery method with a non existant email and the platform must do nothing
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

  Scenario: From the UI, test login via the recovery method with an existant email, the platform must send a
  notification and login when subscribed user follow the sent link
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

  Scenario: From the UI, test login via the recovery method with an existant email of an user with 2FA enables, the
  platform must send a notification and login when subscribed user follow the sent link and input a valid TOTP token.
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
