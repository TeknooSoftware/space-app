Feature: API admin endpoints to create new job and deploy project with encrypted secrets from account's configuration
  In order to deploy project
  As an administrator of Space
  I want to create new jobs from account's projets to deploy them with encrypted secrets from the account's
  configuration

  To run a job, Space will clone the project from its cloning url, install all dependencies and do some other configured
  stuff in the `.paas.yaml` file, build OCI images, push them to the private OCI registry of the account, generate new
  Kubernetes manifest and apply them to the cluster.
  Clusters are defined from the environment passed on the job creation, from the clusters list defined in the project.
  Deployment file can use variables defined at Job's creation. Variables can be persisted on the project or the account
  to be reused easily. Variables defined on the account are shared on all projects and can be overwritten on each
  projects.
  Variables must be available and imported in the new job. Persisted secrets are encrypted

  Scenario: From the API, as Admin, execute a job from a project, with account's encrypted variables, prefix, a valid
  paas file, via a request with a form url encoded body
    Given A Space app instance
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin:
      | field                     | value |
      | new_job.envName           | prod  |
      | new_job.variables.0.name  | FOO   |
      | new_job.variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the API, as Admin, execute a job from a project, with account's encrypted variables, prefix, a valid
  paas file, encrypted messages between workers, via a request with a form url encoded body
    Given A Space app instance
    And encryption capacities between servers and agents
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin:
      | field                     | value |
      | new_job.envName           | prod  |
      | new_job.variables.0.name  | FOO   |
      | new_job.variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the API, as Admin, re-execute a job from a project, with account's encrypted variables, prefix, a
  valid paas file, encrypted messages between workers, via a request with a form url encoded body
    Given A Space app instance
    And encryption capacities between servers and agents
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And "1" jobs for the project
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to restart a the job as admin:
      | field                     | value |
      | new_job.envName           | prod  |
      | new_job.variables.0.name  | FOO   |
      | new_job.variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the API, as Admin, execute a job from a project, with account's encrypted variables, prefix, a valid
  paas file, via a request with a json body
    Given A Space app instance
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the API, as Admin, execute a job from a project, with account's encrypted variables, prefix, a valid
  paas file, encrypted messages between workers, via a request with a json body
    Given A Space app instance
    And encryption capacities between servers and agents
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the API, as Admin, execute a job from a project, with account's encrypted variables, prefix, defined
  quota, without defined resources, a valid paas file, via a request with a json body
    Given A Space app instance
    And encryption capacities between servers and agents
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file without resources
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the API, as Admin, execute a job from a project, with account's encrypted variables, prefix, defined
  quota, with partial defined resources, a valid paas file, via a request with a json body
    Given A Space app instance
    And encryption capacities between servers and agents
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with partial resources
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the API, as Admin, execute a job from a project, with account's encrypted variables, prefix, defined
  quota, with full defined resources, a valid paas file, via a request with a json body
    Given A Space app instance
    And encryption capacities between servers and agents
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with resources
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the API, as Admin, execute a job from a project, with account's encrypted variables, prefix, defined
  quota, with required resources exceeded quota, a valid paas file, via a request with a json body
    Given A Space app instance
    And encryption capacities between servers and agents
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with limited quota
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And it has an error about a quota exceeded
    And no Kubernetes manifests must not be created
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the API, as Admin, execute a job from a project, with account's encrypted variables, prefix, a valid
  paas file with default generic values for variables and all variables are not filled via a request with a json body
    Given A Space app instance
    And encryption capacities between servers and agents
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults for the cluster
    And the account has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    And there are no project persisted variables
    Then the account must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: From the API, as Admin, execute a job from a project, with account's encrypted variables, a valid paas file,
  with new variables to persist after runinto the project, via a request with a json body
    Given A Space app instance
    And encryption capacities between servers and agents
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the account has these persisted variables:
      | id  | name          | secret | value                | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/foo.php | prod        |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin:
      | field                                     | value                   |
      | new_job.envName                           | prod                    |
      | new_job.variables.0.name                  | FOO                     |
      | new_job.variables.0.value                 | BAR                     |
      | new_job.variables.SERVER_SCRIPT.value     | /opt/app/src/server.php |
      | new_job.variables.SERVER_SCRIPT.secret    | 1                       |
      | new_job.variables.SERVER_SCRIPT.persisted | 1                       |
      | new_job.variables.1.name                  | hello                   |
      | new_job.variables.1.value                 | world                   |
      | new_job.variables.1.secret                | 1                       |
      | new_job.variables.1.persisted             | 1                       |
      | new_job.variables.2.name                  | world                   |
      | new_job.variables.2.value                 | hello                   |
      | new_job.variables.2.secret                | 0                       |
      | new_job.variables.2.persisted             | 1                       |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
      | x   | hello         | 1      | world                   | prod        |
      | x   | world         | 0      | hello                   | prod        |
    Then the account must have these persisted variables
      | id  | name          | secret | value                | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/foo.php | prod        |

  Scenario: From the API, as Admin, execute a job from a project, with account's encrypted variables, a valid paas file,
  with new variables to persist after runinto the project, via a request with a json body
    Given A Space app instance
    And encryption capacities between servers and agents
    And encryption of persisted variables in the database
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the account has these persisted variables:
      | id  | name          | secret | value                | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/foo.php | prod        |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field                             | value                   |
      | envName                           | prod                    |
      | variables.0.name                  | FOO                     |
      | variables.0.value                 | BAR                     |
      | variables.SERVER_SCRIPT.value     | /opt/app/src/server.php |
      | variables.SERVER_SCRIPT.secret    | 1                       |
      | variables.SERVER_SCRIPT.persisted | 1                       |
      | variables.1.name                  | hello                   |
      | variables.1.value                 | world                   |
      | variables.1.secret                | 1                       |
      | variables.1.persisted             | 1                       |
      | variables.2.name                  | world                   |
      | variables.2.value                 | hello                   |
      | variables.2.secret                | 0                       |
      | variables.2.persisted             | 1                       |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
      | x   | hello         | 1      | world                   | prod        |
      | x   | world         | 0      | hello                   | prod        |
    Then the account must have these persisted variables
      | id  | name          | secret | value                | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/foo.php | prod        |

