Feature: On a space instance, we can start a job from a project, User can define some variable in the account
  configuration. Variables must be available and imported in the new job.
  Space will clone the project from its cloning url, install all dependencies and do some other configured stuff
  in the `.paas.yaml` file, build OCI images, push them to the private OCI registry of the account, generate new
  Kubernetes manifest and apply them to the cluster

  Scenario: Execute a job from a project, with account's var and it takes too long
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project"
    And the project has a complete paas file
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And simulate a too long image building
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And it has an error about a timeout
    And there are no project persisted variables
    Then the account keeps these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var and it takes too long with a paas file extends
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And extensions libraries provided by administrators
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project"
    And a project with a paas file using extends
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And simulate a too long image building
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And it has an error about a timeout
    And there are no project persisted variables
    Then the account keeps these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with prefix and paas file is valid
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | PROJECT_URL   |
      | new_job.variables.1.value             | <auto>        |
      | new_job.variables.2.name              | FOO           |
      | new_job.variables.2.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with prefix and paas file is valid and override the account's var
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the account has these persisted variables:
      | id  | name          | secret | value                | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/foo.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value                   |
      | new_job._token                        | <auto>                  |
      | new_job.projectId                     | <auto>                  |
      | new_job.newJobId                      | <auto>                  |
      | new_job.envName                       | prod                    |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT           |
      | new_job.variables.SERVER_SCRIPT.value | /opt/app/src/server.php |
      | new_job.variables.1.name              | FOO                     |
      | new_job.variables.1.value             | BAR                     |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/foo.php | prod        |

  Scenario: Execute a job from a project, with account's var with prefix and paas file is valid with encrypted message
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with prefix and paas file is valid with encrypted message with secrets encryptions
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with prefix and defined quota and paas file is valid without resources defined
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file without resources
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with encrypted message and prefix and defined quota and paas file is valid without resources defined
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file without resources
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with encrypted message and with secrets encryptions and prefix and defined quota and paas file is valid without resources defined
    Given A Space app instance
    And encryption capacities between servers and agents
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file without resources
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with prefix and defined quota and paas file is valid with partial resources defined
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with partial resources
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with encrypted message and prefix and defined quota and paas file is valid with partial resources defined
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with partial resources
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with encrypted message and with secrets encryptions and prefix and defined quota and paas file is valid with partial resources defined
    Given A Space app instance
    And encryption capacities between servers and agents
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with partial resources
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with prefix and defined quota and paas file is valid with full resources defined
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with resources
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with encrypted message and prefix and defined quota and paas file is valid with full resources defined
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with resources
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with encrypted message and with secrets encryptions and prefix and defined quota and paas file is valid with full resources defined
    Given A Space app instance
    And encryption capacities between servers and agents
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with resources
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with prefix and defined quota and paas file is valid with quota exceeded
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with limited quota
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And it has an error about a quota exceeded
    And no Kubernetes manifests must not be created
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with encrypted message and prefix and defined quota and paas file is valid with quota exceeded
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with limited quota
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And it has an error about a quota exceeded
    And no Kubernetes manifests must not be created
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with encrypted message and with secrets encryptions and prefix and defined quota and paas file is valid with quota exceeded
    Given A Space app instance
    And encryption capacities between servers and agents
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with limited quota
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And it has an error about a quota exceeded
    And no Kubernetes manifests must not be created
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job, with server's defaults, from a project, with account's var with prefix and paas file is valid and has defaults
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job, with server's defaults, from a project, with account's var with prefix and paas file is valid and has defaults, with encrypted message
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job, with server's defaults, from a project, with account's var with prefix and paas file is valid and has defaults, with encrypted message and with secrets encryptions
    Given A Space app instance
    And encryption capacities between servers and agents
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job, with server's defaults, from a project, with account's var with prefix and paas file is valid and has defaults for the cluster
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults for the cluster
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job, with server's defaults, from a project, with account's var with prefix and paas file is valid and has defaults for the cluster, with encrypted message
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults for the cluster
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job, with server's defaults, from a project, with account's var with prefix and paas file is valid and has defaults for the cluster, with encrypted message and with secrets encryptions
    Given A Space app instance
    And encryption capacities between servers and agents
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults for the cluster
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with prefix and paas file with extends is valid
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And extensions libraries provided by administrators
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And a project with a paas file using extends
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with hierarchical namespace and paas file is valid
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a cluster supporting hierarchical namespace
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project"
    And the project has a complete paas file
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with hierarchical namespace and paas file with extends is valid
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And extensions libraries provided by administrators
    And a cluster supporting hierarchical namespace
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project"
    And a project with a paas file using extends
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with hierarchical namespace and prefix and paas file is valid
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a cluster supporting hierarchical namespace
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "demo"
    And the project has a complete paas file
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with hierarchical namespace and prefix and paas file with extends is valid
    Given A Space app instance
    And a kubernetes client
    And extensions libraries provided by administrators
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a cluster supporting hierarchical namespace
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And a project with a paas file using extends
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with hierarchical namespace and prefix and paas file with extends is valid  with secrets encryptions
    Given A Space app instance
    And encryption of persisted variables in the database
    And a kubernetes client
    And extensions libraries provided by administrators
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a cluster supporting hierarchical namespace
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And a project with a paas file using extends
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                 | value         |
      | new_job._token                        | <auto>        |
      | new_job.projectId                     | <auto>        |
      | new_job.newJobId                      | <auto>        |
      | new_job.envName                       | prod          |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.1.name              | FOO           |
      | new_job.variables.1.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a project, with account's var with prefix and paas file is valid with new persisted vars
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the account has these persisted variables:
      | id  | name          | secret | value                | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/foo.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                     | value                   |
      | new_job._token                            | <auto>                  |
      | new_job.projectId                         | <auto>                  |
      | new_job.newJobId                          | <auto>                  |
      | new_job.envName                           | prod                    |
      | new_job.variables.SERVER_SCRIPT.name      | SERVER_SCRIPT           |
      | new_job.variables.SERVER_SCRIPT.value     | /opt/app/src/server.php |
      | new_job.variables.SERVER_SCRIPT.secret    | 1                       |
      | new_job.variables.SERVER_SCRIPT.persisted | 1                       |
      | new_job.variables.1.name                  | PROJECT_URL             |
      | new_job.variables.1.value                 | <auto>                  |
      | new_job.variables.2.name                  | FOO                     |
      | new_job.variables.2.value                 | BAR                     |
      | new_job.variables.3.name                  | hello                   |
      | new_job.variables.3.value                 | world                   |
      | new_job.variables.3.secret                | 1                       |
      | new_job.variables.3.persisted             | 1                       |
      | new_job.variables.4.name                  | world                   |
      | new_job.variables.4.value                 | hello                   |
      | new_job.variables.4.secret                | 0                       |
      | new_job.variables.4.persisted             | 1                       |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
      | x   | hello         | 1      | world                   | prod        |
      | x   | world         | 0      | hello                   | prod        |
    Then the account must have these persisted variables
      | id  | name          | secret | value                | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/foo.php | prod        |

  Scenario: Execute a job from a project, with encrypted account's var with prefix and paas file is valid with new persisted vars
    Given A Space app instance
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the account has these persisted variables:
      | id  | name          | secret | value                | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/foo.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                                     | value                   |
      | new_job._token                            | <auto>                  |
      | new_job.projectId                         | <auto>                  |
      | new_job.newJobId                          | <auto>                  |
      | new_job.envName                           | prod                    |
      | new_job.variables.SERVER_SCRIPT.name      | SERVER_SCRIPT           |
      | new_job.variables.SERVER_SCRIPT.value     | /opt/app/src/server.php |
      | new_job.variables.SERVER_SCRIPT.secret    | 1                       |
      | new_job.variables.SERVER_SCRIPT.persisted | 1                       |
      | new_job.variables.1.name                  | PROJECT_URL             |
      | new_job.variables.1.value                 | <auto>                  |
      | new_job.variables.2.name                  | FOO                     |
      | new_job.variables.2.value                 | BAR                     |
      | new_job.variables.3.name                  | hello                   |
      | new_job.variables.3.value                 | world                   |
      | new_job.variables.3.secret                | 1                       |
      | new_job.variables.3.persisted             | 1                       |
      | new_job.variables.4.name                  | world                   |
      | new_job.variables.4.value                 | hello                   |
      | new_job.variables.4.secret                | 0                       |
      | new_job.variables.4.persisted             | 1                       |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
      | x   | hello         | 1      | world                   | prod        |
      | x   | world         | 0      | hello                   | prod        |
    Then the account must have these persisted variables
      | id  | name          | secret | value                | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/foo.php | prod        |