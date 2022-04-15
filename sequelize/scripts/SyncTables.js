require("dotenv").config();

var { exit } = require("process");
var Model = require("./../models");

Model.sequelize
  .query("SET FOREIGN_KEY_CHECKS = 0;")
  .then(function () {
    // Re-build tables
    Model.sequelize
      .sync({
        force: true, // Preserves the column order when modified
        alter: true,
        logging: false,
      })
      .then(function () {
        Model.sequelize
          .query("SET FOREIGN_KEY_CHECKS = 1;")
          .then(async function () {
            console.log("Successfully updated table structure.");

            //=================SEED INITIAL DATA=================//
            await Model.app_product_category.create({
              category_name: "Shoes",
            });

            await Model.variant_sizes.create({
              size_name: "XS",
              category_id: 1,
            });

            await Model.products.create({
              product_name: "Nike - XS",
              product_category: 1,
            });

            await Model.app_product.create({
              product_name: "Nike - XS",
              product_category: 1,
              product_cost: 50,
              product_price: 100,
              product_stock: 100,
              status: "active",
              //=============EXPERIMENTAL==============//
              product: 1,
              size_id: 1,
              //=============EXPERIMENTAL==============//
            });
            //=================SEED INITIAL DATA=================//

            exit();
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
