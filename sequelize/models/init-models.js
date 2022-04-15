var DataTypes = require("sequelize").DataTypes;
var _app_custom_price = require("./app_custom_price");
var _app_customer = require("./app_customer");
var _app_order = require("./app_order");
var _app_order_product = require("./app_order_product");
var _app_product = require("./app_product");
var _app_product_category = require("./app_product_category");
var _app_user = require("./app_user");
var _variant_color = require("./variant_color");
var _variant_size = require("./variant_size");

function initModels(sequelize) {
  var app_custom_price = _app_custom_price(sequelize, DataTypes);
  var app_customer = _app_customer(sequelize, DataTypes);
  var app_order = _app_order(sequelize, DataTypes);
  var app_order_product = _app_order_product(sequelize, DataTypes);
  var app_product = _app_product(sequelize, DataTypes);
  var app_product_category = _app_product_category(sequelize, DataTypes);
  var app_user = _app_user(sequelize, DataTypes);
  var variant_color = _variant_color(sequelize, DataTypes);
  var variant_size = _variant_size(sequelize, DataTypes);

  app_custom_price.belongsTo(app_customer, { as: "customer", foreignKey: "customer_id"});
  app_customer.hasMany(app_custom_price, { as: "app_custom_prices", foreignKey: "customer_id"});
  app_order.belongsTo(app_customer, { as: "customer", foreignKey: "customer_id"});
  app_customer.hasMany(app_order, { as: "app_orders", foreignKey: "customer_id"});
  app_order_product.belongsTo(app_order, { as: "order", foreignKey: "order_id"});
  app_order.hasMany(app_order_product, { as: "app_order_products", foreignKey: "order_id"});
  app_custom_price.belongsTo(app_product, { as: "product", foreignKey: "product_id"});
  app_product.hasMany(app_custom_price, { as: "app_custom_prices", foreignKey: "product_id"});
  app_order_product.belongsTo(app_product, { as: "product", foreignKey: "product_id"});
  app_product.hasMany(app_order_product, { as: "app_order_products", foreignKey: "product_id"});
  app_product.belongsTo(app_product_category, { as: "product_category_app_product_category", foreignKey: "product_category"});
  app_product_category.hasMany(app_product, { as: "app_products", foreignKey: "product_category"});
  variant_color.belongsTo(app_product_category, { as: "category", foreignKey: "category_id"});
  app_product_category.hasMany(variant_color, { as: "variant_colors", foreignKey: "category_id"});
  variant_size.belongsTo(app_product_category, { as: "category", foreignKey: "category_id"});
  app_product_category.hasMany(variant_size, { as: "variant_sizes", foreignKey: "category_id"});

  return {
    app_custom_price,
    app_customer,
    app_order,
    app_order_product,
    app_product,
    app_product_category,
    app_user,
    variant_color,
    variant_size,
  };
}
module.exports = initModels;
module.exports.initModels = initModels;
module.exports.default = initModels;
