class ColorPalette {
	readonly name: string;
	readonly slug: string;
	readonly shades: string[];

	constructor(name: string, shades: string[]) {
		this.name = name.trim();
		this.slug = name.trim().toLowerCase();
		this.shades = Array.from(shades).map((shade) => shade.trim().toLowerCase());
	}

	php() {
		return Array.from(this.shades)
			.map((shade, index) => {
				return `array(
\t'name'   => esc_html_x( '${this.name}-${index + 1}00', '', 'go' ),
\t'slug'   => '${this.slug}-${index + 1}00',
\t'color'  => '${shade}',
),
`;
			})
			.join("");
	}

	css() {
		return Array.from(this.shades)
			.map((_shade, index) => {
				return `\t.has-${this.slug}-${index + 1}00-background-color {
\t\tbackground-color: var(--go--color--${this.slug}-${index + 1}00);
\t}

\t.has-${this.slug}-${index + 1}00-color {
\t\tcolor: var(--go--color--${this.slug}-${index + 1}00);
\t}

`;
			})
			.join("");
	}

	vars() {
		return Array.from(this.shades)
			.map((shade, index) => {
				return `\t--go--color--${this.slug}-${index + 1}00: ${shade};\n`;
			})
			.join("");
	}
}

const colors = [
	new ColorPalette("Blue", [
		"#DCEEFB",
		"#B6E0FE",
		"#84C5F4",
		"#62B0E8",
		"#4098D7",
		"#2680C2",
		"#186FAF",
		"#0F609B",
		"#0A558C",
		"#003E6B",
	]),
	new ColorPalette("Yellow", [
		"#FFFBEA",
		"#FFF3C4",
		"#FCE588",
		"#FADB5F",
		"#F7C948",
		"#F0B429",
		"#DE911D",
		"#CB6E17",
		"#B44D12",
		"#8D2B0B",
	]),
	new ColorPalette("Gray", [
		"#F0F4F8",
		"#D9E2EC",
		"#BCCCDC",
		"#9FB3C8",
		"#829AB1",
		"#627D98",
		"#486581",
		"#334E68",
		"#243B53",
		"#102A43",
	]),
	new ColorPalette("Cyan", [
		"#E0FCFF",
		"#BEF8FD",
		"#87EAF2",
		"#54D1DB",
		"#38BEC9",
		"#2CB1BC",
		"#14919B",
		"#0E7C86",
		"#0A6C74",
		"#044E54",
	]),
	new ColorPalette("Red", [
		"#FFEEEE",
		"#FACDCD",
		"#F29B9B",
		"#E66A6A",
		"#D64545",
		"#BA2525",
		"#A61B1B",
		"#911111",
		"#780A0A",
		"#610404",
	]),
];

let vars = "";
for (const color of colors) {
	vars += color.vars();
}

let css = "";
for (const color of colors) {
	css += color.css();
}
let php = "";
for (const color of colors) {
	php += color.php();
}

console.log(vars);
console.log(css);
console.log(php);
