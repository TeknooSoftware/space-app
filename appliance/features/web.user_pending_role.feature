@web
Feature: Web interface of a user attached to no account
  In order to wait for my account, while my settings stay up to date
  As a user of Space attached to no account
  I want to sign in, see a dashboard that explains my situation, and edit only my settings, my password and my 2FA

  A user with ROLE_PENDING is attached to no account. It signs in, but it can only open its dashboard, its settings,
  its password, its 2FA and the support. The API keys, the JWT, the projects, the pages of an account and the
  administration are refused to it. An administrator gives ROLE_USER to this user when it attaches it to an account.

  Background:
    Given a Space app instance
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a pending, called "Martin" "Paul" with the "martin@teknoo.space" with the password "Test2@Test"

  Scenario: From the UI, a pending user without 2FA signs in and sees a dashboard without cluster
    When the user sign in with "martin@teknoo.space" and the password "Test2@Test"
    Then it is redirected to the dashboard
    When it opens the page of the route "space_dashboard"
    Then get a valid web page
    And the page does not show "Demo Kube Cluster"
    And the page shows the message "teknoo.space.text.dashboard.without_account"
    And the page has a link to the route "space_dashboard"
    And the page has a link to the route "space_my_settings"
    And the page has no link to the route "space_project_list"
    And the page has no link to the route "space_account_settings"
    And the page has no link to the route "_teknoo_common_jwt_create"
    And the page has no link to the route "_teknoo_common_api_keys_manage"
    When the user logs out
    Then it is redirected to the login page

  Scenario: From the UI, a pending user with 2FA edits its settings
    Given the 2FA authentication is enabled for the last user
    And the user is signed in with "martin@teknoo.space" and the password "Test2@Test"
    And It goes to user settings
    When it submits the form:
      | field                                          | value               |
      | space_user._token                              | <auto>              |
      | space_user.user.firstName                      | Pierre              |
      | space_user.user.lastName                       | Martin              |
      | space_user.user.email                          | martin@teknoo.space |
      | space_user.user.storedPassword.password.first  |                     |
      | space_user.user.storedPassword.password.second |                     |
    Then its name is now "Pierre Martin"

  Scenario Outline: From the UI, a pending user opens its pages
    Given the 2FA authentication is enabled for the last user
    And the user is signed in with "martin@teknoo.space" and the password "Test2@Test"
    When it opens the page of the route "<route>"
    Then get a valid web page

    Examples:
      | route                            |
      | space_dashboard                  |
      | space_my_settings                |
      | space_update_password            |
      | _teknoo_common_2fa_google_enable |
      | space_support_contact            |

  Scenario Outline: From the UI, a pending user can not open the pages of the users of an account
    Given the 2FA authentication is enabled for the last user
    And the user is signed in with "martin@teknoo.space" and the password "Test2@Test"
    When it opens the page of the route "<route>"
    Then the user must have a 403 error

    Examples:
      | route                           |
      | _teknoo_common_api_keys_manage  |
      | _teknoo_common_jwt_create       |
      | space_project_list              |
      | space_project_new               |
      | space_account_settings          |
      | space_account_status            |
      | space_account_clusters_list     |
      | _teknoo_paas_admin_account_list |
      | _teknoo_common_admin_user_list  |

  Scenario: From the UI, a pending user can not open the dashboard of a cluster
    Given the 2FA authentication is enabled for the last user
    And the user is signed in with "martin@teknoo.space" and the password "Test2@Test"
    When It opens the dashboard frame of "demo-kube-cluster" for "dev"
    Then the user must have a 403 error
