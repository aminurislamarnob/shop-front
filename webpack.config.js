const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
    ...defaultConfig,
    entry: {
        admin: './src/admin.js',
    },
    output: {
        path: path.resolve(__dirname, 'assets/build'),
        filename: '[name]/script.js',
    },
    module: {
        ...defaultConfig.module,
        rules: [
            ...defaultConfig.module.rules,
            // {
            //     test: /\.scss$/i,
            //     use: ['style-loader', 'css-loader', 'sass-loader'],
            // },
            // {
            //     test: /\.css$/i,
            //     use: ['style-loader', 'css-loader', 'postcss-loader'],
            // },
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env', '@babel/preset-react']
                    }
                }
            }
        ],
    },
    resolve: {
        extensions: ['.js', '.jsx']
    }
};