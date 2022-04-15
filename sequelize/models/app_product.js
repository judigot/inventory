const Sequelize = require('sequelize');
module.exports = function(sequelize, DataTypes) {
  return sequelize.define('app_product', {
    product_id: {
      autoIncrement: true,
      type: DataTypes.INTEGER,
      allowNull: false,
      primaryKey: true
    },
    product_name: {
      type: DataTypes.STRING(255),
      allowNull: true
    },
    product_category: {
      type: DataTypes.INTEGER,
      allowNull: false,
      references: {
        model: 'app_product_category',
        key: 'category_id'
      }
    },
    product_cost: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    product_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    product_stock: {
      type: DataTypes.INTEGER,
      allowNull: true,
      defaultValue: 0
    },
    status: {
      type: DataTypes.ENUM('active','inactive'),
      allowNull: true,
      defaultValue: "active"
    }
  }, {
    sequelize,
    tableName: 'app_product',
    timestamps: false,
    charset: 'utf8',
    collate: 'utf8_general_ci',
    indexes: [
      {
        name: "PRIMARY",
        unique: true,
        using: "BTREE",
        fields: [
          { name: "product_id" },
        ]
      },
      {
        name: "product_category",
        using: "BTREE",
        fields: [
          { name: "product_category" },
        ]
      },
    ]
  });
};
