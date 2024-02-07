import fs from 'fs';
import path from 'path';

const svgFolder = './src/icons';
const outputFile = 'svg-symbols.svg';

let output = `<svg xmlns="http://www.w3.org/2000/svg" style="display:none" id="svg-symbols">`;

// Loop through SVG files in folder
fs.readdirSync(svgFolder).forEach(file => {
  if (path.extname(file) === '.svg') {
    // Read SVG file contents
    let svg = fs.readFileSync(path.join(svgFolder, file), 'utf8');

    let width = svg.match(/width="(\d+)"/)[1];
    let height = svg.match(/height="(\d+)"/)[1];
    let viewBox = svg.match(/viewBox="(.+?)"/)[1];

    // Remove outer <svg> tags
    svg = svg.slice(svg.indexOf('>') + 1, svg.lastIndexOf('</svg>'));

    
    // Add to output wrapped in <symbol>
    output += `
      <symbol id="symbol-${path.basename(file, '.svg').replace(/_/g, '-')}" width="${width}" height="${height}" viewBox="${viewBox}">
        ${svg}
      </symbol>
    `;
  }
});

// Close <svg> tag
output += `</svg>`;

// Write output to file
fs.writeFileSync(outputFile, output);

console.log('SVG symbols file generated!');