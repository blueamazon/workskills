var webpack = require('webpack'); 
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
  entry: './src/index.js',
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '../../js/wplms_h5p.js'
  },
  module: {
    rules: [{
      test: /\.scss$/,
      use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader'
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: true,
              // options...
            }
          }
        ]
    },
    {
            exclude: /node_modules/,
            test: /\.js$/,
            loader: 'babel-loader'
        }],

  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: '../../css/wplms_h5p.css',
    }),
  ]
};