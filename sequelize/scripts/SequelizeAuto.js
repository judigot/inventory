require("dotenv").config();
const SequelizeAuto = require("sequelize-auto");

const auto = new SequelizeAuto(
  process.env.DB_DATABASE,
  process.env.DB_USERNAME,
  process.env.DB_PASSWORD,
  {
    host: process.env.DB_HOST,
    dialect: process.env.DB_CONNECTION,
    directory: "./sequelize/models", // where to write files
    port: process.env.DB_PORT,
    noInitModels: true, // Prevent writing the init-models.js file
    singularize: true, // Singularize model and file names from plural table
    // caseModel: "c", // Convert snake_case column names to camelCase field names: user_id -> userId
    // caseFile: "c", // File names created for each model use camelCase.js not snake_case.js
    additional: {
      // Options added to each model

      // timestamps: true,
      charset: "utf8",
      collate: "utf8_general_ci",
    },
    // tables: ["Users", "Posts"], // use all tables, if omitted
  }
);

auto.run().then((data) => {
  console.log(data.tables); // table and field list
  console.log(data.foreignKeys); // table foreign key list
  console.log(data.indexes); // table indexes
  console.log(data.hasTriggerTables); // tables that have triggers
  console.log(data.relations); // relationships between models
  console.log(data.text); // text of generated models
});
