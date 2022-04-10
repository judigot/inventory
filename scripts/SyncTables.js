require("dotenv").config();

var Model = require("./../models");

Model.sequelize
  .query("SET FOREIGN_KEY_CHECKS = 0;")
  .then(function () {
    // Re-build tables
    Model.sequelize
      .sync({
        // force: true,
        alter: true,
        logging: false,
      })
      .then(function () {
        Model.sequelize.query("SET FOREIGN_KEY_CHECKS = 1;").then(function () {
          console.log("Successfully updated table structure.");
          // exit();
        });
      })
      .catch(function (error) {
        console.log(error);
      });
  })
  .catch((error) => {
    // Failure
    console.log(error);
  })
  .finally(() => {
    // Finally
  });
