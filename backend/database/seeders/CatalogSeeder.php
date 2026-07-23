<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Library\Domains\Authors\Models\Author;
use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Categories\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * A catalogue of real titles rather than generated noise.
 *
 * The screens are read by people judging whether the product looks like a
 * library, and "Lorem ipsum dolor" by "Consuelo Rivas" tells them nothing. Real
 * books also give the search, the category filters and the popular-author
 * report something meaningful to return.
 */
class CatalogSeeder extends Seeder
{
    /**
     * @var list<array{name: string, description: string}>
     */
    private const CATEGORIES = [
        ['name' => 'Narrativa', 'description' => 'Novela y relato contemporáneo y clásico.'],
        ['name' => 'Historia', 'description' => 'Ensayo histórico y biografías.'],
        ['name' => 'Infantil', 'description' => 'Lecturas para las primeras edades.'],
        ['name' => 'Ciencia', 'description' => 'Divulgación científica y técnica.'],
        ['name' => 'Poesía', 'description' => 'Verso y antologías poéticas.'],
        ['name' => 'Ensayo', 'description' => 'Pensamiento, filosofía y crítica.'],
        ['name' => 'Novela gráfica', 'description' => 'Cómic y narrativa ilustrada.'],
        ['name' => 'Policiaca', 'description' => 'Novela negra, misterio e intriga.'],
    ];

    /**
     * title, author, publisher, year, categories.
     *
     * @var list<array{0: string, 1: string, 2: string, 3: int, 4: list<string>}>
     */
    private const BOOKS = [
        ['Cien años de soledad', 'Gabriel García Márquez', 'Sudamericana', 1967, ['Narrativa']],
        ['El amor en los tiempos del cólera', 'Gabriel García Márquez', 'Oveja Negra', 1985, ['Narrativa']],
        ['Crónica de una muerte anunciada', 'Gabriel García Márquez', 'La Oveja Negra', 1981, ['Narrativa', 'Policiaca']],
        ['La casa de los espíritus', 'Isabel Allende', 'Plaza & Janés', 1982, ['Narrativa']],
        ['Paula', 'Isabel Allende', 'Plaza & Janés', 1994, ['Narrativa', 'Ensayo']],
        ['Rayuela', 'Julio Cortázar', 'Sudamericana', 1963, ['Narrativa']],
        ['Bestiario', 'Julio Cortázar', 'Sudamericana', 1951, ['Narrativa']],
        ['Ficciones', 'Jorge Luis Borges', 'Sur', 1944, ['Narrativa']],
        ['El Aleph', 'Jorge Luis Borges', 'Losada', 1949, ['Narrativa']],
        ['Pedro Páramo', 'Juan Rulfo', 'Fondo de Cultura Económica', 1955, ['Narrativa']],
        ['La ciudad y los perros', 'Mario Vargas Llosa', 'Seix Barral', 1963, ['Narrativa']],
        ['Conversación en La Catedral', 'Mario Vargas Llosa', 'Seix Barral', 1969, ['Narrativa']],
        ['Los detectives salvajes', 'Roberto Bolaño', 'Anagrama', 1998, ['Narrativa']],
        ['2666', 'Roberto Bolaño', 'Anagrama', 2004, ['Narrativa']],
        ['Nada', 'Carmen Laforet', 'Destino', 1945, ['Narrativa']],
        ['La sombra del viento', 'Carlos Ruiz Zafón', 'Planeta', 2001, ['Narrativa', 'Policiaca']],
        ['El infinito en un junco', 'Irene Vallejo', 'Siruela', 2019, ['Ensayo', 'Historia']],
        ['Sapiens: de animales a dioses', 'Yuval Noah Harari', 'Debate', 2011, ['Historia', 'Ensayo']],
        ['Homo Deus', 'Yuval Noah Harari', 'Debate', 2015, ['Historia', 'Ensayo']],
        ['Una historia de España', 'Arturo Pérez-Reverte', 'Alfaguara', 2019, ['Historia']],
        ['El capitán Alatriste', 'Arturo Pérez-Reverte', 'Alfaguara', 1996, ['Narrativa', 'Historia']],
        ['La tabla de Flandes', 'Arturo Pérez-Reverte', 'Alfaguara', 1990, ['Policiaca']],
        ['Los pilares de la Tierra', 'Ken Follett', 'Plaza & Janés', 1989, ['Narrativa', 'Historia']],
        ['La caída de los gigantes', 'Ken Follett', 'Plaza & Janés', 2010, ['Narrativa', 'Historia']],
        ['El nombre de la rosa', 'Umberto Eco', 'Lumen', 1980, ['Narrativa', 'Policiaca']],
        ['1984', 'George Orwell', 'Debolsillo', 1949, ['Narrativa']],
        ['Rebelión en la granja', 'George Orwell', 'Destino', 1945, ['Narrativa']],
        ['Un mundo feliz', 'Aldous Huxley', 'Debolsillo', 1932, ['Narrativa']],
        ['Fahrenheit 451', 'Ray Bradbury', 'Minotauro', 1953, ['Narrativa']],
        ['Crónicas marcianas', 'Ray Bradbury', 'Minotauro', 1950, ['Narrativa']],
        ['Matilda', 'Roald Dahl', 'Alfaguara', 1988, ['Infantil']],
        ['Charlie y la fábrica de chocolate', 'Roald Dahl', 'Alfaguara', 1964, ['Infantil']],
        ['El BFG', 'Roald Dahl', 'Alfaguara', 1982, ['Infantil']],
        ['Manolito Gafotas', 'Elvira Lindo', 'Seix Barral', 1994, ['Infantil']],
        ['El principito', 'Antoine de Saint-Exupéry', 'Salamandra', 1943, ['Infantil', 'Narrativa']],
        ['Donde viven los monstruos', 'Maurice Sendak', 'Kalandraka', 1963, ['Infantil']],
        ['Momo', 'Michael Ende', 'Alfaguara', 1973, ['Infantil', 'Narrativa']],
        ['La historia interminable', 'Michael Ende', 'Alfaguara', 1979, ['Infantil', 'Narrativa']],
        ['Breve historia del tiempo', 'Stephen Hawking', 'Crítica', 1988, ['Ciencia']],
        ['El universo en una cáscara de nuez', 'Stephen Hawking', 'Crítica', 2001, ['Ciencia']],
        ['Cosmos', 'Carl Sagan', 'Planeta', 1980, ['Ciencia']],
        ['El mundo y sus demonios', 'Carl Sagan', 'Planeta', 1995, ['Ciencia', 'Ensayo']],
        ['El gen egoísta', 'Richard Dawkins', 'Salvat', 1976, ['Ciencia']],
        ['Astrofísica para gente con prisas', 'Neil deGrasse Tyson', 'Paidós', 2017, ['Ciencia']],
        ['La vida secreta de los árboles', 'Peter Wohlleben', 'Obelisco', 2015, ['Ciencia']],
        ['Pensar rápido, pensar despacio', 'Daniel Kahneman', 'Debate', 2011, ['Ensayo', 'Ciencia']],
        ['Veinte poemas de amor y una canción desesperada', 'Pablo Neruda', 'Nascimento', 1924, ['Poesía']],
        ['Canto general', 'Pablo Neruda', 'Océano', 1950, ['Poesía']],
        ['Poeta en Nueva York', 'Federico García Lorca', 'Séneca', 1940, ['Poesía']],
        ['Romancero gitano', 'Federico García Lorca', 'Revista de Occidente', 1928, ['Poesía']],
        ['Antología poética', 'Alejandra Pizarnik', 'Lumen', 2015, ['Poesía']],
        ['Los girasoles ciegos', 'Alberto Méndez', 'Anagrama', 2004, ['Narrativa', 'Historia']],
        ['Persépolis', 'Marjane Satrapi', 'Norma', 2000, ['Novela gráfica', 'Historia']],
        ['Maus', 'Art Spiegelman', 'Reservoir Books', 1986, ['Novela gráfica', 'Historia']],
        ['El árabe del futuro', 'Riad Sattouf', 'Salamandra', 2014, ['Novela gráfica']],
        ['Arrugas', 'Paco Roca', 'Astiberri', 2007, ['Novela gráfica']],
        ['La casa', 'Paco Roca', 'Astiberri', 2015, ['Novela gráfica']],
        ['El silencio de la ciudad blanca', 'Eva García Sáenz de Urturi', 'Planeta', 2016, ['Policiaca']],
        ['La chica del tren', 'Paula Hawkins', 'Planeta', 2015, ['Policiaca']],
        ['Los hombres que no amaban a las mujeres', 'Stieg Larsson', 'Destino', 2005, ['Policiaca']],
        ['El silencio de los corderos', 'Thomas Harris', 'Debolsillo', 1988, ['Policiaca']],
        ['Asesinato en el Orient Express', 'Agatha Christie', 'Espasa', 1934, ['Policiaca']],
        ['Diez negritos', 'Agatha Christie', 'Espasa', 1939, ['Policiaca']],
        ['Patria', 'Fernando Aramburu', 'Tusquets', 2016, ['Narrativa', 'Historia']],
        ['Los renglones torcidos de Dios', 'Torcuato Luca de Tena', 'Planeta', 1979, ['Narrativa']],
        ['La península de las casas vacías', 'David Uclés', 'Siruela', 2024, ['Narrativa', 'Historia']],
        ['Feria', 'Ana Iris Simón', 'Círculo de Tiza', 2020, ['Ensayo']],
        ['Ordesa', 'Manuel Vilas', 'Alfaguara', 2018, ['Narrativa']],
        ['Lectura fácil', 'Cristina Morales', 'Anagrama', 2018, ['Narrativa']],
        ['Las malas', 'Camila Sosa Villada', 'Tusquets', 2019, ['Narrativa']],
        ['Panza de burro', 'Andrea Abreu', 'Barrett', 2020, ['Narrativa']],
        ['El corazón de las tinieblas', 'Joseph Conrad', 'Alianza', 1899, ['Narrativa']],
        ['Moby Dick', 'Herman Melville', 'Cátedra', 1851, ['Narrativa']],
        ['Frankenstein', 'Mary Shelley', 'Alianza', 1818, ['Narrativa']],
        ['Orgullo y prejuicio', 'Jane Austen', 'Alba', 1813, ['Narrativa']],
        ['Jane Eyre', 'Charlotte Brontë', 'Alba', 1847, ['Narrativa']],
        ['Cumbres borrascosas', 'Emily Brontë', 'Alba', 1847, ['Narrativa']],
        ['La metamorfosis', 'Franz Kafka', 'Alianza', 1915, ['Narrativa']],
        ['El proceso', 'Franz Kafka', 'Alianza', 1925, ['Narrativa']],
        ['Los hermanos Karamázov', 'Fiódor Dostoyevski', 'Alba', 1880, ['Narrativa']],
        ['Crimen y castigo', 'Fiódor Dostoyevski', 'Alba', 1866, ['Narrativa', 'Policiaca']],
    ];

    public function run(): void
    {
        /** @var array<string, int> $categoryIds */
        $categoryIds = [];
        foreach (self::CATEGORIES as $category) {
            $categoryIds[$category['name']] = Category::query()->create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
            ])->id;
        }

        /** @var array<string, int> $authorIds */
        $authorIds = [];
        foreach (array_unique(array_column(self::BOOKS, 1)) as $name) {
            $authorIds[$name] = Author::query()->create(['name' => $name])->id;
        }

        foreach (self::BOOKS as $index => [$title, $authorName, $publisher, $year, $categoryNames]) {
            // A small library holds few copies of most titles and several of the
            // ones that circulate; a flat number would make availability
            // uninteresting on screen.
            $totalCopies = match (true) {
                $index % 11 === 0 => 6,
                $index % 5 === 0 => 4,
                $index % 3 === 0 => 3,
                default => 2,
            };

            $book = new Book([
                'title' => $title,
                'isbn' => $this->isbn13($index),
                'description' => $this->description($title, $authorName, $year),
                'publisher' => $publisher,
                'publication_year' => $year,
                'total_copies' => $totalCopies,
            ]);
            $book->available_copies = $totalCopies;
            $book->created_at = now()->subDays(random_int(5, 900));
            $book->save();

            $book->authors()->attach($authorIds[$authorName]);
            $book->categories()->attach(
                array_map(fn (string $name): int => $categoryIds[$name], $categoryNames),
            );
        }
    }

    /**
     * A checksum-valid ISBN-13 derived from the row, so the value the catalogue
     * shows would survive the validation the API applies on write (FR-VAL-3).
     */
    private function isbn13(int $index): string
    {
        $body = '978'.str_pad((string) (4000000 + $index * 7919), 9, '0', STR_PAD_LEFT);

        $sum = 0;
        foreach (str_split($body) as $position => $digit) {
            $sum += (int) $digit * ($position % 2 === 0 ? 1 : 3);
        }

        return $body.((10 - $sum % 10) % 10);
    }

    private function description(string $title, string $author, int $year): string
    {
        return sprintf(
            '%s, de %s (%d). Ejemplar del fondo general; consulta en sala y préstamo a domicilio.',
            $title,
            $author,
            $year,
        );
    }
}
