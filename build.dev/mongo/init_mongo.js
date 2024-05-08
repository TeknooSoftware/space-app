let conn = new Mongo();
db = conn.getDB("teknoo_space");

db.createUser(
  {
    user: "space_user",
    pwd: "space_pwd",
    roles: [
      { role: "readWrite", db: "teknoo_space" }
    ],
    "mechanisms" : ["SCRAM-SHA-256"],
    "passwordDigestor": "server"
  }
);

db.users.insert(
  {
    "_id" : "92c70ddccbda3c85985d84e70037f218",
    "created_at" : "2017-09-14T22:19:32.775Z",
    "deleted_at" : null,
    "email" : "space@teknoo.software",
    "first_name" : "Space",
    "last_name" : "Admin",
    "roles" : [
      "ROLE_USER",
      "ROLE_ADMIN"
    ],
    "updated_at" : ISODate("2021-09-21T06:20:19.022+0000"),
    "authData" : [
      {
        "type" : "Teknoo\\East\\Common\\Object\\StoredPassword",
        "hash" : "$argon2id$v=19$m=65536,t=4,p=1$1dnP5CUbHhQL1yK4zAeqtQ$GH/y8s7AtHS27ip+yeWRn9DA95RP7F02R7QOSJCu6Ac",
        "salt" : "",
        "algo" : "Teknoo\\East\\CommonBundle\\Object\\PasswordAuthenticatedUser"
      }
    ]
  }
);
