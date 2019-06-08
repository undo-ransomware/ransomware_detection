module.exports = {
	plugins: [
		['@babel/plugin-syntax-dynamic-import'],
		["@babel/transform-runtime"]
		],
	presets: [
		[
			'@babel/preset-env',
			{
				targets: {
					browsers: ['last 2 versions', 'ie >= 11']
				}
			}
		]
	]
}
