Feature: Web interface to create new job and deploy project with variables from project's configuration
  In order to deploy project
  As an user of an account
  I want to create new jobs from account's projets to deploy them with variables from the project's configuration

  To run a job, Space will clone the project from its cloning url, install all dependencies and do some other configured
  stuff in the `.paas.yaml` file, build OCI images, push them to the private OCI registry of the account, generate new
  Kubernetes manifest and apply them to the cluster.
  Clusters are defined from the environment passed on the job creation, from the clusters list defined in the project.
  Deployment file can use variables defined at Job's creation. Variables can be persisted on the project or the account
  to be reused easily. Variables defined on the account are shared on all projects and can be overwritten on each
  projects.

  Scenario: From the UI, execute a job from an owned project, with project's variables, a valid paas file, simulate a
  too long image building and get and error
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
    And a standard project "my project"
    And the project has a complete paas file
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    But it has an error about a timeout
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, a valid paas file using extends,
  simulate a too long image building and get and error
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
    And a standard project "my project"
    And the project has a complete paas file using extends
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    But it has an error about a timeout
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, a valid paas file
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | PROJECT_URL   |
      | new_job.variables.0.value             | <auto>        |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
      | new_job.variables.2.name              | FOO           |
      | new_job.variables.2.value             | BAR           |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, a valid paas file and
  the project's variable is overrided
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO                     |
      | new_job.variables.0.value             | BAR                     |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT           |
      | new_job.variables.SERVER_SCRIPT.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/foo.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, a valid paas file,
  encrypted messages between workers
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's encrypted variables, prefix, a valid
  paas file
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, defined quota, without
  defined resources
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file without resources
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, defined quota, without
  defined resources, a valid paas file, encrypted messages between workers
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file without resources
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, defined quota,
  without defined resources, a valid paas file, encrypted messages between workers
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file without resources
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, defined quota, with
  partial defined resources, a valid paas file
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with partial resources
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, defined quota, with
  partial defined resources, a valid paas file, encrypted messages between workers
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with partial resources
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's encrypted variables, prefix, defined
  quota, with partial defined resources, a valid paas file, encrypted messages between workers
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with partial resources
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, defined quota, with
  full defined resources, a valid paas file
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with resources
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, defined quota, with
  fully defined resources, a valid paas file, encrypted messages between workers
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with resources
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's encrypted variables, prefix, defined
  quota, with full defined resources, a valid paas file, encrypted messages between workers
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with resources
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, defined quota, with
  required resources exceeded quota, a valid paas file
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with limited quota
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And it has an error about a quota exceeded
    And no Kubernetes manifests must not be created
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, defined quota, with
  required resources exceeded quota, a valid paas file, encrypted messages between workers
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with limited quota
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And it has an error about a quota exceeded
    And no Kubernetes manifests must not be created
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, defined quota, with
  required resources exceeded quota, a valid paas file, encrypted messages between workers
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with limited quota
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And it has an error about a quota exceeded
    And no Kubernetes manifests must not be created
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's encrypted variables, prefix, a valid
  paas file with default generic values for variables and all variables are not filled
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, a valid paas file with
  default generic values for variables, encrypted messages between workers and all variables are not filled
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's encrypted variables, prefix, a valid
  paas file with default generic values for variables and all variables are not filled
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, a valid paas file with
  default values for variables dedicated to the cluster and all variables are not filled
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults for the cluster
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, a valid paas file with
  default values for variables dedicated to the cluster, encrypted messages between workers and all variables are not
  filled
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults for the cluster
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with encrypted project's secrets, prefix, a valid paas
  file with default values for variables dedicated to the cluster, encrypted messages between workers and all variables
  are not filled
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults for the cluster
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, a valid paas file
  using extends
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file using extends
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, a valid paas file,
  on cluster supporting hierarchical namespace
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
    And a standard project "my project"
    And the project has a complete paas file
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, a valid paas file
  using extends, on cluster supporting hierarchical namespace
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
    And a standard project "my project"
    And the project has a complete paas file using extends
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, a valid paas fileon cluster
  supporting hierarchical namespace
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
    And a standard project "my project" and a prefix "demo"
    And the project has a complete paas file
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, a valid paas file
  using extends, on cluster supporting hierarchical namespace
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file using extends
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with encrypted project's secrets, prefix, a valid paas
  file using extends, on cluster supporting hierarchical namespace
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file using extends
    And the project has these persisted variables:
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
      | new_job.variables.0.name              | FOO           |
      | new_job.variables.0.value             | BAR           |
      | new_job.variables.SERVER_SCRIPT.name  | SERVER_SCRIPT |
      | new_job.variables.SERVER_SCRIPT.value | <auto>        |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, a valid paas file,
  with new variables to persist after run into the project
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the project has these persisted variables:
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
      | new_job.variables.0.name                  | PROJECT_URL             |
      | new_job.variables.0.value                 | <auto>                  |
      | new_job.variables.SERVER_SCRIPT.name      | SERVER_SCRIPT           |
      | new_job.variables.SERVER_SCRIPT.value     | /opt/app/src/server.php |
      | new_job.variables.SERVER_SCRIPT.secret    | 1                       |
      | new_job.variables.SERVER_SCRIPT.persisted | 1                       |
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
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
      | x   | hello         | 1      | world                   | prod        |
      | x   | world         | 0      | hello                   | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's encrypted variables, a valid paas file,
  with new variables to persist after run into the project
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the project has these persisted variables:
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
      | new_job.variables.0.name                  | PROJECT_URL             |
      | new_job.variables.0.value                 | <auto>                  |
      | new_job.variables.SERVER_SCRIPT.name      | SERVER_SCRIPT           |
      | new_job.variables.SERVER_SCRIPT.value     | /opt/app/src/server.php |
      | new_job.variables.SERVER_SCRIPT.secret    | 1                       |
      | new_job.variables.SERVER_SCRIPT.persisted | 1                       |
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
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
      | x   | hello         | 1      | world                   | prod        |
      | x   | world         | 0      | hello                   | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, a valid paas file,
  using conditions and encrypted messages between workers
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file using conditions
    And the project has these persisted variables:
      | id  | name          | secret | value                | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/foo.php | prod        |
      | bbb | PHP_VERSION   | 1      | 7.4                  | prod        |
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
      | new_job.variables.5.name                  | ENV                     |
      | new_job.variables.5.value                 | prod                    |
      | new_job.variables.6.name                  | PHP_VERSION             |
      | new_job.variables.6.value                 | 7.4                     |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
      | bbb | PHP_VERSION   | 1      | 7.4                     | prod        |
      | x   | hello         | 1      | world                   | prod        |
      | x   | world         | 0      | hello                   | prod        |

  Scenario: From the UI, execute a job from an owned project, with project's variables, prefix, a valid paas file,
  jobs and encrypted messages between workers
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
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with jobs
    And the project has these persisted variables:
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
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
      | x   | hello         | 1      | world                   | prod        |
      | x   | world         | 0      | hello                   | prod        |
