const Sequelize = require("sequelize");
module.exports = function (sequelize, DataTypes) {
  return sequelize.define(
    "variant_sizes",
    {
      id: {
        autoIncrement: true,
        type: DataTypes.INTEGER,
        allowNull: false,
        primaryKey: true,
      },
      size_name: {
        type: DataTypes.STRING,
        allowNull: false,
      },
      category_id: {
        type: DataTypes.INTEGER,
        allowNull: false,
        //==========FOREIGN KEY==========//
        onUpdate: "CASCADE",
        references: {
          model: "app_product_category",
          key: "category_id",
        },
        //==========FOREIGN KEY==========//
      },
    },
    {
      sequelize,
      tableName: "variant_sizes",
      timestamps: false,
      indexes: [
        {
          name: "PRIMARY",
          unique: true,
          using: "BTREE",
          fields: [{ name: "id" }],
        },
        {
          name: "category_id",
          using: "BTREE",
          fields: [{ name: "category_id" }],
        },
      ],
    }
  );
};
