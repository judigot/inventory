const Sequelize = require("sequelize");
module.exports = function (sequelize, DataTypes) {
  return sequelize.define(
    "products",
    {
      id: {
        autoIncrement: true,
        type: DataTypes.INTEGER,
        allowNull: false,
        primaryKey: true,
      },
      product_name: {
        type: DataTypes.STRING(255),
        allowNull: false,
      },
      product_category: {
        type: DataTypes.INTEGER,
        allowNull: false,
        references: {
          model: "app_product_category",
          key: "category_id",
        },
      },
    },
    {
      sequelize,
      tableName: "products",
      timestamps: false,
      charset: "utf8",
      collate: "utf8_general_ci",
      indexes: [
        {
          name: "PRIMARY",
          unique: true,
          using: "BTREE",
          fields: [{ name: "id" }],
        },
        {
          name: "product_category",
          using: "BTREE",
          fields: [{ name: "product_category" }],
        },
      ],
    }
  );
};
