const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const TerserWebpackPlugin = require('terser-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');

module.exports = {
  mode: 'production',
  optimization: {
    minimizer: [
      new TerserWebpackPlugin({
        test: /\.js(\?.*)?$/i,
        sourceMap: true,
      }),
      new OptimizeCSSAssetsPlugin({

      }),
    ]
  },
  devtool: "source-map",
  entry: {
    main: './webpack/src/index.js',
  },
  output: {
    path: path.resolve(__dirname, '../web'),
    filename: 'js/bundle.min.js',
  },
  module: {
    rules: [
      {
        test: /\.css$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
          },
          'css-loader',
        ]
      },
      {
        test: /\.(woff|woff2|otf|eot|ttf|svg)(\?v=\d+\.\d+\.\d+)?$/,
        use: {
          loader: 'url-loader',
          options: {
            limit: 8192,
            name: '[name].[ext]',
            outputPath: 'fonts',
            publicPath: '../fonts/'
          }
        },
      },
      {
        test: /\.(jpe?g|png|gif|svg)$/i,
        exclude: [/fonts/],
        use: [
          {
            loader: 'url-loader',
            options: {
              limit: 8192,
              name: '[name].[ext]',
              outputPath: 'img',
              //publicPath: '../img/'
            }
          },
          {
            loader: 'img-loader',
            options: {
              name: '[name].[ext]',
              outputPath: 'img',
              //publicPath: '../img/'
            }
          },
        ],
      },
      {
        test: /\.m?js$/,
        //exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/env']
          }
        }
      },
      {
        test: require.resolve('jquery'),
        use: [{
          loader: 'expose-loader',
          options: 'jQuery'
        },{
          loader: 'expose-loader',
          options: '$'
        }],
      },
      {
        test: require.resolve('inputmask'),
        use: [{
          loader: 'expose-loader',
          options: 'inputmask'
        }],
      },
      {
        test: require.resolve('notifier-js'),
        use: [{
          loader: 'expose-loader',
          options: 'notifier'
        }],
      },
    ]
  },
  plugins: [
    new MiniCssExtractPlugin({
      path: path.resolve(__dirname, '../web/css'),
      filename: 'css/bundle.min.css',
    }),
    new webpack.ProvidePlugin(
      {
        Popper: 'popper.js/dist/umd/popper.min.js',
        $: 'jquery',
        'jQuery': 'jquery',
        'inputmask': 'inputmask',
      }
    ),
  ],
};
