Feature: On a space instance, an API is available to run jobs with some deployment's variable defined in the project.
  Variables must be available and imported in the new job.
  Space will clone the project from its cloning url, install all dependencies and do some other configured stuff
  in the `.paas.yaml` file, build OCI images, push them to the private OCI registry of the account, generate new
  Kubernetes manifest and apply them to the cluster

  Scenario: Execute a job from an owned project, with project's var, prefix and paas file is valid with url encoded body
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job:
      | field                     | value |
      | new_job.envName           | prod  |
      | new_job.variables.0.name  | FOO   |
      | new_job.variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from an owned project with project's var, prefix and paas file is valid with url encoded body with encrypted message
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job:
      | field                     | value |
      | new_job.envName           | prod  |
      | new_job.variables.0.name  | FOO   |
      | new_job.variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Re-execute a job from an owned project with project's var, prefix and paas file is valid with url encoded body with encrypted message
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And "1" jobs for the project
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to restart a the job:
      | field                     | value |
      | new_job.envName           | prod  |
      | new_job.variables.0.name  | FOO   |
      | new_job.variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a non-owned project with project's var, prefix and paas file is valid with url encoded body
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
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job:
      | field                     | value |
      | new_job.envName           | prod  |
      | new_job.variables.0.name  | FOO   |
      | new_job.variables.0.value | BAR   |
    Then get a JSON reponse
    And an 403 error
    Then the project keeps these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from an owned project with project's var, prefix and paas file is valid with a json body
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from an owned project with project's var, prefix and paas file is valid with a json body with encrypted message
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from an owned project with project's var, prefix and defined quota and paas file is valid without resources defined and the request has a json body
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from an owned project with project's var, prefix and defined quota and paas file is valid without resources defined and the request has a json body
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from an owned project with project's var, prefix and defined quota and paas file is valid without partial resources defined and the request has a json body
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from an owned project with project's var, prefix and defined quota and paas file is valid without partial resources defined and the request has a json body
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from an owned project with project's var, prefix and defined quota and paas file is valid without full resources defined and the request has a json body
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from an owned project with project's var, prefix and defined quota and paas file is valid full partial resources defined and the request has a json body
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from an owned project with project's var, prefix and defined quota and paas file is valid with quota exceeded and the request has a json body
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And it has an error about a quota exceeded
    And no Kubernetes manifests must not be created
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from an owned project with project's var, prefix and defined quota and paas file is valid with with quota exceeded and the request has a json body
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And it has an error about a quota exceeded
    And no Kubernetes manifests must not be created
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job, with server's defaults, from a project with project's var, prefix and paas file is valid and has defaults for the cluster
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job, with server's defaults, from a project with project's var, prefix and paas file is valid and has defaults for the cluster, with encrypted message
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from a non-owned project with project's var, prefix and paas file is valid with a json body
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
    And an account for "An Other Company" with the account namespace "my-company"
    And an user, called "Dupond" "Albert" with the "albert@teknoo.space" with the password "Test2@Test"
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And an 403 error
    Then the project keeps these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from an owned project with project's var, prefix and paas file with extends is valid with a json body
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from an owned project with project's var, hierarchical namespace and paas file is valid with a json body
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from an owned project with project's var, hierarchical namespace and paas file with extends is valid with json body
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from an owned project with project's var, hierarchical namespace and prefix and paas file is valid with json body
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from an owned project with project's var, hierarchical namespace and prefix and paas file with extends is valid with a json body
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
      | field             | value |
      | envName           | prod  |
      | variables.0.name  | FOO   |
      | variables.0.value | BAR   |
    Then get a JSON reponse
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |

  Scenario: Execute a job from an owned project, with project's var, prefix and paas file is valid with url encoded body
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/foo.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job:
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
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
      | x   | hello         | 1      | world                   | prod        |
      | x   | world         | 0      | hello                   | prod        |

  Scenario: Execute a job from an owned project with project's var, prefix and paas file is valid with a json body
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
    And the project has these persisted variables:
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/foo.php | prod        |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job with a json body:
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
    And a pending job id
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
    Then the project must have these persisted variables
      | id  | name          | secret | value                   | environment |
      | aaa | SERVER_SCRIPT | 1      | /opt/app/src/server.php | prod        |
      | x   | hello         | 1      | world                   | prod        |
      | x   | world         | 0      | hello                   | prod        |
