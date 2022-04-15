const Sequelize = require('sequelize');
module.exports = function(sequelize, DataTypes) {
  return sequelize.define('app_customer', {
    customer_id: {
      autoIncrement: true,
      type: DataTypes.INTEGER,
      allowNull: false,
      primaryKey: true
    },
    first_name: {
      type: DataTypes.STRING(255),
      allowNull: true
    },
    last_name: {
      type: DataTypes.STRING(255),
      allowNull: true
    },
    client_address: {
      type: DataTypes.STRING(255),
      allowNull: true
    },
    date_added: {
      type: DataTypes.DATEONLY,
      allowNull: true
    },
    status: {
      type: DataTypes.ENUM('active','inactive'),
      allowNull: false,
      defaultValue: "active"
    },
    jumbo_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    jumbo_c_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    xl_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    xl_c_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    l_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    l_c_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    m_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    m_c_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    s_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    s_c_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    p_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    p_c_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    pwe_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    pwe_c_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    d2_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    d2_c_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    marble_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    marble_c_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    d1b_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    d1b_c_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    d1s_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    d1s_c_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    b1_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    b1_c_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    b2_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    b2_c_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    b3_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    b3_c_price: {
      type: DataTypes.FLOAT,
      allowNull: true,
      defaultValue: 0
    },
    es_price: {
      type: DataTypes.FLOAT,
      allowNull: false,
      defaultValue: 0
    }
  }, {
    sequelize,
    tableName: 'app_customer',
    timestamps: false,
    charset: 'utf8',
    collate: 'utf8_general_ci',
    indexes: [
      {
        name: "PRIMARY",
        unique: true,
        using: "BTREE",
        fields: [
          { name: "customer_id" },
        ]
      },
    ]
  });
};
