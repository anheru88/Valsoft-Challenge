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
        ['name' => 'Fiction', 'description' => 'Novels and short stories, classic and contemporary.'],
        ['name' => 'History', 'description' => 'Historical essays and biography.'],
        ['name' => "Children's", 'description' => 'Reading for the earliest ages.'],
        ['name' => 'Science', 'description' => 'Popular science and technology.'],
        ['name' => 'Poetry', 'description' => 'Verse and poetry anthologies.'],
        ['name' => 'Essay', 'description' => 'Thought, philosophy and criticism.'],
        ['name' => 'Graphic novel', 'description' => 'Comics and illustrated narrative.'],
        ['name' => 'Crime', 'description' => 'Crime fiction, mystery and suspense.'],
    ];

    /**
     * title, author, publisher, year, categories.
     *
     * @var list<array{0: string, 1: string, 2: string, 3: int, 4: list<string>}>
     */
    private const BOOKS = [
        ['One Hundred Years of Solitude', 'Gabriel García Márquez', 'Penguin', 1967, ['Fiction']],
        ['Love in the Time of Cholera', 'Gabriel García Márquez', 'Penguin', 1985, ['Fiction']],
        ['Chronicle of a Death Foretold', 'Gabriel García Márquez', 'Vintage', 1981, ['Fiction', 'Crime']],
        ['The House of the Spirits', 'Isabel Allende', 'Bantam', 1982, ['Fiction']],
        ['Hopscotch', 'Julio Cortázar', 'Pantheon', 1963, ['Fiction']],
        ['Ficciones', 'Jorge Luis Borges', 'Grove Press', 1944, ['Fiction']],
        ['The Aleph', 'Jorge Luis Borges', 'Penguin', 1949, ['Fiction']],
        ['Pedro Páramo', 'Juan Rulfo', 'Grove Press', 1955, ['Fiction']],
        ['The Time of the Hero', 'Mario Vargas Llosa', 'Faber & Faber', 1963, ['Fiction']],
        ['The Savage Detectives', 'Roberto Bolaño', 'Picador', 1998, ['Fiction']],
        ['2666', 'Roberto Bolaño', 'Picador', 2004, ['Fiction']],
        ['The Shadow of the Wind', 'Carlos Ruiz Zafón', 'Weidenfeld & Nicolson', 2001, ['Fiction', 'Crime']],
        ['Papyrus', 'Irene Vallejo', 'Hodder & Stoughton', 2019, ['Essay', 'History']],
        ['Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', 'Harvill Secker', 2011, ['History', 'Essay']],
        ['Homo Deus', 'Yuval Noah Harari', 'Harvill Secker', 2015, ['History', 'Essay']],
        ['The Pillars of the Earth', 'Ken Follett', 'Macmillan', 1989, ['Fiction', 'History']],
        ['Fall of Giants', 'Ken Follett', 'Macmillan', 2010, ['Fiction', 'History']],
        ['The Name of the Rose', 'Umberto Eco', 'Secker & Warburg', 1980, ['Fiction', 'Crime']],
        ['Nineteen Eighty-Four', 'George Orwell', 'Secker & Warburg', 1949, ['Fiction']],
        ['Animal Farm', 'George Orwell', 'Secker & Warburg', 1945, ['Fiction']],
        ['Brave New World', 'Aldous Huxley', 'Chatto & Windus', 1932, ['Fiction']],
        ['Fahrenheit 451', 'Ray Bradbury', 'Simon & Schuster', 1953, ['Fiction']],
        ['The Martian Chronicles', 'Ray Bradbury', 'Doubleday', 1950, ['Fiction']],
        ['Matilda', 'Roald Dahl', 'Jonathan Cape', 1988, ["Children's"]],
        ['Charlie and the Chocolate Factory', 'Roald Dahl', 'Alfred A. Knopf', 1964, ["Children's"]],
        ['The BFG', 'Roald Dahl', 'Jonathan Cape', 1982, ["Children's"]],
        ['The Little Prince', 'Antoine de Saint-Exupéry', 'Reynal & Hitchcock', 1943, ["Children's", 'Fiction']],
        ['Where the Wild Things Are', 'Maurice Sendak', 'Harper & Row', 1963, ["Children's"]],
        ['Momo', 'Michael Ende', 'Puffin', 1973, ["Children's", 'Fiction']],
        ['The Neverending Story', 'Michael Ende', 'Puffin', 1979, ["Children's", 'Fiction']],
        ['A Brief History of Time', 'Stephen Hawking', 'Bantam', 1988, ['Science']],
        ['The Universe in a Nutshell', 'Stephen Hawking', 'Bantam', 2001, ['Science']],
        ['Cosmos', 'Carl Sagan', 'Random House', 1980, ['Science']],
        ['The Demon-Haunted World', 'Carl Sagan', 'Random House', 1995, ['Science', 'Essay']],
        ['The Selfish Gene', 'Richard Dawkins', 'Oxford University Press', 1976, ['Science']],
        ['Astrophysics for People in a Hurry', 'Neil deGrasse Tyson', 'W. W. Norton', 2017, ['Science']],
        ['The Hidden Life of Trees', 'Peter Wohlleben', 'Greystone Books', 2015, ['Science']],
        ['Thinking, Fast and Slow', 'Daniel Kahneman', 'Farrar, Straus and Giroux', 2011, ['Essay', 'Science']],
        ['Silent Spring', 'Rachel Carson', 'Houghton Mifflin', 1962, ['Science', 'Essay']],
        ['The Immortal Life of Henrietta Lacks', 'Rebecca Skloot', 'Crown', 2010, ['Science']],
        ['Twenty Love Poems and a Song of Despair', 'Pablo Neruda', 'Jonathan Cape', 1924, ['Poetry']],
        ['Poet in New York', 'Federico García Lorca', 'Grove Press', 1940, ['Poetry']],
        ['Gypsy Ballads', 'Federico García Lorca', 'Penguin', 1928, ['Poetry']],
        ['Ariel', 'Sylvia Plath', 'Faber & Faber', 1965, ['Poetry']],
        ['The Waste Land', 'T. S. Eliot', 'Faber & Faber', 1922, ['Poetry']],
        ['Leaves of Grass', 'Walt Whitman', 'Penguin', 1855, ['Poetry']],
        ['Persepolis', 'Marjane Satrapi', 'Pantheon', 2000, ['Graphic novel', 'History']],
        ['Maus', 'Art Spiegelman', 'Pantheon', 1986, ['Graphic novel', 'History']],
        ['The Arab of the Future', 'Riad Sattouf', 'Metropolitan Books', 2014, ['Graphic novel']],
        ['Wrinkles', 'Paco Roca', 'Fantagraphics', 2007, ['Graphic novel']],
        ['Watchmen', 'Alan Moore', 'DC Comics', 1987, ['Graphic novel']],
        ['The Girl with the Dragon Tattoo', 'Stieg Larsson', 'Norstedts', 2005, ['Crime']],
        ['The Girl on the Train', 'Paula Hawkins', 'Riverhead Books', 2015, ['Crime']],
        ['The Silence of the Lambs', 'Thomas Harris', 'St. Martin\'s Press', 1988, ['Crime']],
        ['Murder on the Orient Express', 'Agatha Christie', 'Collins Crime Club', 1934, ['Crime']],
        ['And Then There Were None', 'Agatha Christie', 'Collins Crime Club', 1939, ['Crime']],
        ['The Big Sleep', 'Raymond Chandler', 'Alfred A. Knopf', 1939, ['Crime']],
        ['Gone Girl', 'Gillian Flynn', 'Crown', 2012, ['Crime']],
        ['Heart of Darkness', 'Joseph Conrad', 'Blackwood', 1899, ['Fiction']],
        ['Moby-Dick', 'Herman Melville', 'Harper & Brothers', 1851, ['Fiction']],
        ['Frankenstein', 'Mary Shelley', 'Lackington', 1818, ['Fiction']],
        ['Pride and Prejudice', 'Jane Austen', 'T. Egerton', 1813, ['Fiction']],
        ['Jane Eyre', 'Charlotte Brontë', 'Smith, Elder & Co.', 1847, ['Fiction']],
        ['Wuthering Heights', 'Emily Brontë', 'Thomas Cautley Newby', 1847, ['Fiction']],
        ['The Metamorphosis', 'Franz Kafka', 'Kurt Wolff Verlag', 1915, ['Fiction']],
        ['The Trial', 'Franz Kafka', 'Verlag Die Schmiede', 1925, ['Fiction']],
        ['The Brothers Karamazov', 'Fyodor Dostoevsky', 'The Russian Messenger', 1880, ['Fiction']],
        ['Crime and Punishment', 'Fyodor Dostoevsky', 'The Russian Messenger', 1866, ['Fiction', 'Crime']],
        ['Mrs Dalloway', 'Virginia Woolf', 'Hogarth Press', 1925, ['Fiction']],
        ['To the Lighthouse', 'Virginia Woolf', 'Hogarth Press', 1927, ['Fiction']],
        ['Beloved', 'Toni Morrison', 'Alfred A. Knopf', 1987, ['Fiction']],
        ['Things Fall Apart', 'Chinua Achebe', 'Heinemann', 1958, ['Fiction']],
        ['The Handmaid\'s Tale', 'Margaret Atwood', 'McClelland & Stewart', 1985, ['Fiction']],
        ['Never Let Me Go', 'Kazuo Ishiguro', 'Faber & Faber', 2005, ['Fiction']],
        ['The Remains of the Day', 'Kazuo Ishiguro', 'Faber & Faber', 1989, ['Fiction']],
        ['Midnight\'s Children', 'Salman Rushdie', 'Jonathan Cape', 1981, ['Fiction', 'History']],
        ['A Fine Balance', 'Rohinton Mistry', 'McClelland & Stewart', 1995, ['Fiction']],
        ['The God of Small Things', 'Arundhati Roy', 'Random House', 1997, ['Fiction']],
        ['Norwegian Wood', 'Haruki Murakami', 'Kodansha', 1987, ['Fiction']],
        ['Kafka on the Shore', 'Haruki Murakami', 'Harvill Secker', 2002, ['Fiction']],
        ['Educated', 'Tara Westover', 'Random House', 2018, ['Essay', 'History']],
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
            '%s, by %s (%d). Held in the general collection; available for reading room use and home loan.',
            $title,
            $author,
            $year,
        );
    }
}
