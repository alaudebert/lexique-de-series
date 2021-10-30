<?php

namespace App\Entity;

use App\Entity\ExternalRating;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Series
 *
 * @ORM\Table(name="series", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQ_3A10012D85489131", columns={"imdb"})})
 * @ORM\Entity
 */
class Series
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=128, nullable=false)
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="plot", type="text", length=0, nullable=true)
     */
    private $plot;

    /**
     * @var string
     *
     * @ORM\Column(name="imdb", type="string", length=128, nullable=false)
     */
    private $imdb;

    /**
     * @var string|null
     *
     * @ORM\Column(name="poster", type="blob", length=0, nullable=true)
     */
    private $poster;

    /**
     * @var string|null
     *
     * @ORM\Column(name="director", type="string", length=128, nullable=true)
     */
    private $director;

    /**
     * @var string|null
     *
     * @ORM\Column(name="youtube_trailer", type="string", length=128, nullable=true)
     */
    private $youtubeTrailer;

    /**
     * @var string|null
     *
     * @ORM\Column(name="awards", type="text", length=0, nullable=true)
     */
    private $awards;

    /**
     * @var int|null
     *
     * @ORM\Column(name="year_start", type="integer", nullable=true)
     */
    private $yearStart;

    /**
     * @var int|null
     *
     * @ORM\Column(name="year_end", type="integer", nullable=true)
     */
    private $yearEnd;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Actor", mappedBy="series")
     */
    private $actor;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Country", mappedBy="series")
     */
    private $country;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Genre", mappedBy="series")
     */
    private $genre;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="User", mappedBy="series")
     */
    private $user;

    /**
     * @var \Season
     *
     * @ORM\OneToMany(targetEntity="Season", mappedBy="series")
     * @OrderBy({"number" = "ASC"})
     */
    private $seasons;

    /**
     * @var \Rating
     *
     * @ORM\OneToMany(targetEntity="Rating", mappedBy="series")
     * @OrderBy({"date" = "DESC"})
     */
    private $ratings;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->actor = new \Doctrine\Common\Collections\ArrayCollection();
        $this->country = new \Doctrine\Common\Collections\ArrayCollection();
        $this->genre = new \Doctrine\Common\Collections\ArrayCollection();
        $this->user = new \Doctrine\Common\Collections\ArrayCollection();
        $this->series = new ArrayCollection();
        $this->seasons = new ArrayCollection();
        $this->episodes = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPlot(): ?string
    {
        return $this->plot;
    }

    public function setPlot(?string $plot): self
    {
        $this->plot = $plot;

        return $this;
    }

    public function getImdb(): ?string
    {
        return $this->imdb;
    }

    public function setImdb(string $imdb): self
    {
        $this->imdb = $imdb;

        return $this;
    }

    public function getPoster()
    {
        return $this->poster;
    }

    public function setPoster($poster): self
    {
        $this->poster = $poster;

        return $this;
    }

    public function getDirector(): ?string
    {
        return $this->director;
    }

    public function setDirector(?string $director): self
    {
        $this->director = $director;

        return $this;
    }

    public function getYoutubeTrailer(): ?string
    {
        return $this->youtubeTrailer;
    }

    public function setYoutubeTrailer(?string $youtubeTrailer): self
    {
        $this->youtubeTrailer = $youtubeTrailer;

        return $this;
    }

    public function getAwards(): ?string
    {
        return $this->awards;
    }

    public function setAwards(?string $awards): self
    {
        $this->awards = $awards;

        return $this;
    }

    public function getYearStart(): ?int
    {
        return $this->yearStart;
    }

    public function setYearStart(?int $yearStart): self
    {
        $this->yearStart = $yearStart;

        return $this;
    }

    public function getYearEnd(): ?int
    {
        return $this->yearEnd;
    }

    public function setYearEnd(?int $yearEnd): self
    {
        $this->yearEnd = $yearEnd;

        return $this;
    }

    /**
     * @return Collection|Actor[]
     */
    public function getActor(): Collection
    {
        return $this->actor;
    }

    public function addActor(Actor $actor): self
    {
        if (!$this->actor->contains($actor)) {
            $this->actor[] = $actor;
            $actor->addSeries($this);
        }

        return $this;
    }

    public function removeActor(Actor $actor): self
    {
        if ($this->actor->removeElement($actor)) {
            $actor->removeSeries($this);
        }

        return $this;
    }

    /**
     * @return Collection|Country[]
     */
    public function getCountry(): Collection
    {
        return $this->country;
    }

    public function addCountry(Country $country): self
    {
        if (!$this->country->contains($country)) {
            $this->country[] = $country;
            $country->addSeries($this);
        }

        return $this;
    }

    public function removeCountry(Country $country): self
    {
        if ($this->country->removeElement($country)) {
            $country->removeSeries($this);
        }

        return $this;
    }

    /**
     * @return Collection|Genre[]
     */
    public function getGenre(): Collection
    {
        return $this->genre;
    }

    public function addGenre(Genre $genre): self
    {
        if (!$this->genre->contains($genre)) {
            $this->genre[] = $genre;
            $genre->addSeries($this);
        }

        return $this;
    }

    public function removeGenre(Genre $genre): self
    {
        if ($this->genre->removeElement($genre)) {
            $genre->removeSeries($this);
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user[] = $user;
            $user->addSeries($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->user->removeElement($user)) {
            $user->removeSeries($this);
        }

        return $this;
    }

    public function userHasComment(User $user) {
        foreach($this->getRatings() as $rate) {
            if($rate->getUser()->getId() == $user->getId()) {
                return true;
            }
        }
        return false;
    }

    public function getUserComment(User $user) {
        foreach($this->getRatings() as $rate) {
            if($rate->getUser()->getId() == $user->getId()) {
                return $rate;
            }
        }
        return null;
    }

    /**
     * @return Collection|Season[]
     */
    public function getSeries(): Collection
    {
        return $this->series;
    }

    public function addSeries(Season $series): self
    {
        if (!$this->series->contains($series)) {
            $this->series[] = $series;
            $series->setSeries($this);
        }

        return $this;
    }

    public function removeSeries(Season $series): self
    {
        if ($this->series->removeElement($series)) {
            // set the owning side to null (unless already changed)
            if ($series->getSeries() === $this) {
                $series->setSeries(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Season[]
     */
    public function getSeasons(): Collection
    {
        return $this->seasons;
    }

    public function getNbSeasons() {
        $nb = 0;
        foreach($this->getSeasons() as $lsea) {
            $nb++;
        }
        return $nb;
    }

    public function getNbEpisodes() {
        $nb = 0;
        foreach($this->getSeasons() as $lsea) {
            $nb += $lsea->getNbEpisodes();
        }
        return $nb;        
    }

    public function addSeason(Season $season): self
    {
        if (!$this->seasons->contains($season)) {
            $this->seasons[] = $season;
            $season->setSeries($this);
        }

        return $this;
    }

    public function removeSeason(Season $season): self
    {
        if ($this->seasons->removeElement($season)) {
            // set the owning side to null (unless already changed)
            if ($season->getSeries() === $this) {
                $season->setSeries(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Rating[]
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): self
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings[] = $rating;
            $rating->setSeries($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getSeries() === $this) {
                $rating->setSeries(null);
            }
        }

        return $this;
    }

    public function getNoteMoyenne() {
        $moyenne = null;
        $value = 0;
        $nbElem = 0;
        foreach ($this->getRatings() as $rating) {
            $nbElem++;
            $value+=$rating->getValue();
        }

        if($nbElem > 0) { $moyenne = ($value/$nbElem); }
        return $moyenne;
    }

    public function hasGenre($genre) {
        foreach($this->getGenre() as $g) {
            if($g->getName() === $genre) {
                return true;
            }
        }
        return false;
    }

    public function getMoyImdbSerie() {
        $moy = null;
        $result = 0;
        foreach($this->getSeasons() as $sea) {
            $result++;
            $moy += $sea->getMoyImdbSaison();
        }
        $moy = ($moy == null) ? null : $moy/$result;
        return $moy;
    }

    public function getMoyNoteUtilisateurs() {
        $moy = null;
        $result = 0;
        foreach($this->getRatings() as $rate) {
            $result++;
            $moy += $rate->getValue();
        }
        $moy = ($moy == null) ? null : $moy/$result;
        return $moy;
    }

    public function getNoteMoyGenerale() {
        if($this->getMoyImdbSerie() !=null && $this->getMoyNoteUtilisateurs() != null) {
            return (($this->getMoyImdbSerie() + $this->getMoyNoteUtilisateurs())/2);
        } else {
            return null;
        }
        
    }

    public function getNbNote() {
        $result = 0;
        foreach($this->getRatings() as $rate) {
            $result++;
        }
        return $result;
    }

    public function getNbNoteCommentee() {
        $result = 0;
        foreach($this->getRatings() as $rate) {
            if($rate->getComment() != null && $rate->getComment() !== "") {
                $result++;
            }
        }
        return $result;
    }

    public function __toString()
    {
        return $this->getTitle();
    }

    public function getExternalRatings(): ?ExternalRating
    {
        return $this->externalRatings;
    }

    public function setExternalRatings(?ExternalRating $externalRatings): self
    {
        $this->externalRatings = $externalRatings;

        return $this;
    }


}
