require("dotenv").config();

const Sequelize = require("sequelize");

const sequelize = new Sequelize({
  database: process.env.DB_DATABASE,
  username: process.env.DB_USERNAME,
  password: process.env.DB_PASSWORD,
  host: process.env.DB_HOST,
  dialect: process.env.DB_CONNECTION,
  port: process.env.DB_POST,

  // Disable SQL logging in console
  logging: false,
});

var Model = require("./models/init-models");

Model(sequelize)
  .app_product.findAll()
  .then((result) => {
    // Success
    console.log(result);
  })
  .catch((error) => {
    // Failure
    console.log(error);
  })
  .finally(() => {
    // Finally
  });
