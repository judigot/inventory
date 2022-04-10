var DataTypes = require("sequelize").DataTypes;
var _Post = require("./post");
var _PowerUser = require("./powerUser");
var _User = require("./user");
var _app_custom_price = require("./appCustomPrice");
var _app_customer = require("./appCustomer");
var _app_order = require("./appOrder");
var _app_order_product = require("./appOrderProduct");
var _app_product = require("./appProduct");
var _app_product_category = require("./appProductCategory");
var _app_user = require("./appUser");

function initModels(sequelize) {
  var Post = _Post(sequelize, DataTypes);
  var PowerUser = _PowerUser(sequelize, DataTypes);
  var User = _User(sequelize, DataTypes);
  var app_custom_price = _app_custom_price(sequelize, DataTypes);
  var app_customer = _app_customer(sequelize, DataTypes);
  var app_order = _app_order(sequelize, DataTypes);
  var app_order_product = _app_order_product(sequelize, DataTypes);
  var app_product = _app_product(sequelize, DataTypes);
  var app_product_category = _app_product_category(sequelize, DataTypes);
  var app_user = _app_user(sequelize, DataTypes);

  Post.belongsTo(User, { as: "postOwner_User", foreignKey: "postOwner"});
  User.hasMany(Post, { as: "Posts", foreignKey: "postOwner"});
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

  return {
    Post,
    PowerUser,
    User,
    app_custom_price,
    app_customer,
    app_order,
    app_order_product,
    app_product,
    app_product_category,
    app_user,
  };
}
module.exports = initModels;
module.exports.initModels = initModels;
module.exports.default = initModels;
