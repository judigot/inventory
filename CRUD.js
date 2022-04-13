const Model = require("./sequelize/models");

Model.app_order
  .findAll()
  .then((result) => {
    for (let i = 0; i < result.length; i++) {
      const row = result[i];
      console.log(row.dataValues);
    }
  })
  .catch((error) => {})
  .finally(() => {});
